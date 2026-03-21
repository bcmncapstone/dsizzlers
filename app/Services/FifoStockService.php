<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockIn;

class FifoStockService
{
    /**
     * Validates and allocates checkout quantity from oldest stock-in lots first.
     *
     * Returns allocation details for the current checkout request.
     * Throws RuntimeException when FIFO-available stock is insufficient.
     */
    public function allocateForCheckout(Item $item, int $requestedQuantity): array
    {
        if ($requestedQuantity <= 0) {
            throw new \RuntimeException('Invalid quantity detected in checkout.');
        }

        $snapshot = $this->buildRemainingLotsSnapshot($item, true);
        $workingLots = $snapshot['lots'];
        $available = $snapshot['available'];

        if ($available < $requestedQuantity) {
            throw new \RuntimeException("Insufficient FIFO stock for {$item->item_name}. Available: {$available}, Required: {$requestedQuantity}");
        }

        $remainingToAllocate = $requestedQuantity;
        $allocations = [];

        foreach ($workingLots as &$lot) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            if ($lot['quantity_remaining'] <= 0) {
                continue;
            }

            $take = min($lot['quantity_remaining'], $remainingToAllocate);
            $lot['quantity_remaining'] -= $take;
            $remainingToAllocate -= $take;

            $allocations[] = [
                'stock_in_id' => $lot['stock_in_id'],
                'quantity' => $take,
                'received_date' => $lot['received_date'],
                'source' => $lot['source'] ?? 'stock_in',
            ];
        }
        unset($lot);

        return [
            'available_before_allocation' => $available,
            'allocations' => $allocations,
        ];
    }

    /**
     * Returns current lot-by-lot FIFO balance for display/reporting.
     */
    public function getRemainingLots(Item $item): array
    {
        $snapshot = $this->buildRemainingLotsSnapshot($item, false);

        $lots = array_values(array_filter($snapshot['lots'], function (array $lot) {
            return ($lot['quantity_remaining'] ?? 0) > 0;
        }));

        return [
            'item_id' => (int) $item->item_id,
            'item_name' => (string) $item->item_name,
            'stock_quantity' => max((int) $item->stock_quantity, 0),
            'fifo_available' => $snapshot['available'],
            'lots' => $lots,
        ];
    }

    private function buildRemainingLotsSnapshot(Item $item, bool $lockForUpdate): array
    {
        $currentStock = max((int) $item->stock_quantity, 0);

        $lotsQuery = StockIn::query()
            ->where('item_id', $item->item_id)
            ->orderBy('received_date', 'asc')
            ->orderBy('stock_in_id', 'asc');

        if ($lockForUpdate) {
            $lotsQuery->lockForUpdate();
        }

        $lots = $lotsQuery->get(['stock_in_id', 'quantity_received', 'received_date']);

        $workingLots = [];
        foreach ($lots as $lot) {
            $workingLots[] = [
                'stock_in_id' => (int) $lot->stock_in_id,
                'quantity_remaining' => max((int) $lot->quantity_received, 0),
                'received_date' => $lot->received_date ? (string) $lot->received_date : null,
                'source' => 'stock_in',
            ];
        }

        $totalFromLots = array_sum(array_column($workingLots, 'quantity_remaining'));

        // Backward compatibility: if historical lots are incomplete, treat the difference as legacy opening balance.
        if ($totalFromLots < $currentStock) {
            array_unshift($workingLots, [
                'stock_in_id' => null,
                'quantity_remaining' => $currentStock - $totalFromLots,
                'received_date' => null,
                'source' => 'legacy_balance',
            ]);
            $totalFromLots = $currentStock;
        }

        $historicalConsumed = $totalFromLots - $currentStock;
        $this->consumeFromOldestLots($workingLots, $historicalConsumed);

        return [
            'available' => array_sum(array_column($workingLots, 'quantity_remaining')),
            'lots' => $workingLots,
        ];
    }

    private function consumeFromOldestLots(array &$lots, int $quantityToConsume): void
    {
        $remainingToConsume = max($quantityToConsume, 0);

        foreach ($lots as &$lot) {
            if ($remainingToConsume <= 0) {
                break;
            }

            if ($lot['quantity_remaining'] <= 0) {
                continue;
            }

            $consume = min($lot['quantity_remaining'], $remainingToConsume);
            $lot['quantity_remaining'] -= $consume;
            $remainingToConsume -= $consume;
        }
        unset($lot);
    }
}
