<?php

namespace App\Services;

use App\Models\FranchiseeStock;
use App\Models\StockTransaction;

class FranchiseeFifoStockService
{
    /**
     * Returns current FIFO lots for display in franchisee stock page.
     *
     * @return array{stock_quantity:int,fifo_available:int,lots:array<int,array{quantity_remaining:int,received_at:?string,source:string}>}
     */
    public function getRemainingLots(FranchiseeStock $stock): array
    {
        $snapshot = $this->buildRemainingLotsSnapshot($stock, false);

        return [
            'stock_quantity' => max((int) $stock->current_quantity, 0),
            'fifo_available' => (int) $snapshot['available'],
            // Keep exhausted lots visible so fully consumed items still show their batch history.
            'lots' => array_values($snapshot['lots']),
        ];
    }

    /**
     * Validates deduction and allocates from oldest inbound lots first.
     *
     * @return array{available_before_allocation:int,allocations:array<int,array{quantity:int,source:string,received_at:?string}>}
     */
    public function allocateForDeduction(FranchiseeStock $stock, int $requestedQuantity): array
    {
        if ($requestedQuantity <= 0) {
            throw new \RuntimeException('Invalid deduction quantity.');
        }

        $snapshot = $this->buildRemainingLotsSnapshot($stock, true);
        $workingLots = $snapshot['lots'];
        $available = $snapshot['available'];

        if ($available < $requestedQuantity) {
            throw new \RuntimeException(
                "Insufficient FIFO stock for {$stock->item->item_name}. Available: {$available}, Required: {$requestedQuantity}"
            );
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
                'quantity' => $take,
                'source' => $lot['source'],
                'received_at' => $lot['received_at'],
            ];
        }
        unset($lot);

        return [
            'available_before_allocation' => $available,
            'allocations' => $allocations,
        ];
    }

    /**
     * @return array{available:int,lots:array<int,array{quantity_remaining:int,received_at:?string,source:string}>}
     */
    private function buildRemainingLotsSnapshot(FranchiseeStock $stock, bool $lockForUpdate): array
    {
        $currentStock = max((int) $stock->current_quantity, 0);

        $query = StockTransaction::query()
            ->where('franchisee_id', $stock->franchisee_id)
            ->where('item_id', $stock->item_id)
            ->orderBy('created_at', 'asc')
            ->orderBy('transaction_id', 'asc');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $transactions = $query->get(['transaction_type', 'quantity', 'created_at']);

        $workingLots = [];

        foreach ($transactions as $transaction) {
            $quantity = (int) $transaction->quantity;

            if ($transaction->transaction_type === 'in' && $quantity > 0) {
                $workingLots[] = [
                    'quantity_remaining' => $quantity,
                    'received_at' => $transaction->created_at ? (string) $transaction->created_at : null,
                    'source' => 'stock_in',
                ];
                continue;
            }

            if ($transaction->transaction_type === 'adjustment' && $quantity > 0) {
                $workingLots[] = [
                    'quantity_remaining' => $quantity,
                    'received_at' => $transaction->created_at ? (string) $transaction->created_at : null,
                    'source' => 'manual_add',
                ];
            }
        }

        $totalFromLots = array_sum(array_column($workingLots, 'quantity_remaining'));

        if ($totalFromLots < $currentStock) {
            array_unshift($workingLots, [
                'quantity_remaining' => $currentStock - $totalFromLots,
                'received_at' => null,
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

    /**
     * @param array<int,array{quantity_remaining:int,received_at:?string,source:string}> $lots
     */
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
