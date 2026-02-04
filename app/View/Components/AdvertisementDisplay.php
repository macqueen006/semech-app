<?php

namespace App\View\Components;

use App\Models\Advertisement;
use Illuminate\View\Component;
use Illuminate\Support\Collection;

class AdvertisementDisplay extends Component
{
    public string $position;
    public int $limit;
    public ?string $size;
    public int $offset;
    public Collection $advertisements;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $position = 'sidebar',
        int $limit = 1,
        ?string $size = null,
        int $offset = 0
    ) {
        $this->position = $position;
        $this->limit = $limit;
        $this->size = $size;
        $this->offset = $offset;

        // Fetch advertisements
        $this->advertisements = $this->fetchAdvertisements();

        // Track impressions
        $this->trackImpressions();
    }

    /**
     * Fetch active advertisements
     */
    private function fetchAdvertisements(): Collection
    {
        $query = Advertisement::where('is_active', true)
            ->where('position', $this->position)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });

        if ($this->size) {
            $query->where('size', $this->size);
        }

        return $query
            ->orderBy('display_order')
            ->skip($this->offset)
            ->take($this->limit)
            ->get();
    }

    /**
     * Track advertisement impressions
     */
    private function trackImpressions(): void
    {
        if ($this->advertisements->isEmpty()) {
            return;
        }

        $sessionKey = 'ad_impressions_' . session()->getId();
        $trackedAds = session()->get($sessionKey, []);

        foreach ($this->advertisements as $ad) {
            // Only track if not already tracked in this session
            if (!in_array($ad->id, $trackedAds)) {
                $ad->recordImpression();
                $trackedAds[] = $ad->id;
            }
        }

        // Store tracked ads in session (expires with session)
        session()->put($sessionKey, $trackedAds);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.advertisement-display');
    }
}
