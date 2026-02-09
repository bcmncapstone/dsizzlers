<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class MergeExistingStaffOrders extends Command
{
    protected $signature = 'orders:merge-staff-orders';
    protected $description = 'Merge existing delivered staff orders into franchisee stock';

    public function handle()
    {
        $this->info('Checking for delivered staff orders that need to be merged...');

        // Get all delivered staff orders
        $deliveredStaffOrders = Order::with('orderDetails')
            ->whereNotNull('fstaff_id')
            ->where('order_status', 'Delivered')
            ->get();

        $this->info("Found {$deliveredStaffOrders->count()} delivered staff orders.");

        $mergedCount = 0;
        $skippedCount = 0;

        foreach ($deliveredStaffOrders as $order) {
            // Check if already merged
            $existingMerge = StockTransaction::where('reference_type', 'staff_order')
                ->where('reference_id', $order->order_id)
                ->exists();

            if ($existingMerge) {
                $this->warn("Order #{$order->order_id} already merged, skipping.");
                $skippedCount++;
                continue;
            }

            DB::beginTransaction();
            try {
                foreach ($order->orderDetails as $detail) {
                    // Find or create franchisee stock record
                    $stock = FranchiseeStock::firstOrCreate(
                        [
                            'franchisee_id' => $order->franchisee_id,
                            'item_id' => $detail->item_id,
                        ],
                        [
                            'current_quantity' => 0,
                            'minimum_quantity' => 10,
                        ]
                    );

                    // Update stock quantity
                    $oldQuantity = $stock->current_quantity;
                    $stock->current_quantity += $detail->quantity;
                    $stock->save();

                    // Record the transaction
                    StockTransaction::create([
                        'franchisee_id' => $order->franchisee_id,
                        'item_id' => $detail->item_id,
                        'transaction_type' => 'in',
                        'quantity' => $detail->quantity,
                        'balance_after' => $stock->current_quantity,
                        'reference_type' => 'staff_order',
                        'reference_id' => $order->order_id,
                        'notes' => "Staff order #{$order->order_id} delivered - items added to stock (migrated)",
                        'performed_by_type' => 'system',
                        'performed_by_id' => null,
                    ]);

                    $this->line("  - Added {$detail->quantity} of item #{$detail->item_id} (from {$oldQuantity} to {$stock->current_quantity})");
                }

                DB::commit();
                $this->info("✓ Order #{$order->order_id} merged successfully!");
                $mergedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("✗ Failed to merge order #{$order->order_id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Merged: {$mergedCount}");
        $this->info("Skipped: {$skippedCount}");
        $this->info("Total: {$deliveredStaffOrders->count()}");

        return 0;
    }
}
