<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class RecalculateMealCostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productIds;

    /**
     * Create a new job instance.
     *
     * @param array $productIds IDs of products (ingredients) that were updated.
     * @return void
     */
    public function __construct(array $productIds)
    {
        $this->productIds = $productIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info("RecalculateMealCostsJob: Starting for products: " . implode(', ', $this->productIds));
            
            // Find all meals/compounds that use these ingredients
            $affectedMeals = Product::whereIn('product_type', ['meal', 'compound'])
                ->whereHas('recipes', function($q) {
                    $q->whereIn('ingredient_product_id', $this->productIds);
                })
                ->get();

            Log::info("RecalculateMealCostsJob: Found " . $affectedMeals->count() . " affected meals.");

            foreach ($affectedMeals as $meal) {
                $meal->recalculateMealCost();
            }

            Log::info("RecalculateMealCostsJob: Finished successfully.");
        } catch (\Exception $e) {
            Log::error("RecalculateMealCostsJob Failed: " . $e->getMessage());
        }
    }
}
