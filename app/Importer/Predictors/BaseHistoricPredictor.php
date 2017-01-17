<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 17/01/2017
 * Time: 1:05 PM
 */

namespace App\Importer\Predictors;

use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


abstract class BaseHistoricPredictor
{
    /**
     * @var string
     */
    protected $custodian;

    /**
     * @var Collection
     */
    protected $providerLogs;

    public function __construct(
        $custodian,
        $providerLogs
    ) {
        $this->custodian = $custodian;

        if (is_array($providerLogs)) {
            $providerLogs = new Collection($providerLogs);
        }

        $this->providerLogs = $providerLogs;
    }

    protected function makePrediction(
        $label,
        $addressesPredictions,
        $custodianPredictions,
        $providersPredictions
    ) {
        $merged = $custodianPredictions->merge($providersPredictions)
            ->merge($addressesPredictions)
            ->groupBy("$label")
            ->map(function (
                $item,
                $key
            ) use
            (
                $label
            ) {
                $collection = new Collection($item);

                return [
                    $label        => $key,
                    'total_count' => $collection->sum('total_count'),
                ];
            })
            ->values()
            ->sortByDesc('total_count');


        return $merged->first()[$label] ?? null;
    }

    protected function addressesLookup(
        $label,
        $weightMultiplier = 1
    ) {
        //return an empty collection if the lookup value is empty
        if (!$this->providerLogs) {
            return new Collection();
        }

        $results = ProviderLog::select(DB::raw("*, count('$label') as total_count"))
            ->whereNotNull("$label")
            ->where('ml_ignore', '=', false)
            ->whereNull('first_name')
            ->whereNull('last_name')
            ->where(function ($query) {
                $query->whereIn('street', $this->providerLogs->pluck('street'))
                    ->whereIn('city', $this->providerLogs->pluck('city'))
                    ->whereIn('state', $this->providerLogs->pluck('state'))
                    ->whereIn('zip', $this->providerLogs->pluck('zip'))
                    ->orWhereIn('cell_phone', $this->providerLogs->pluck('cell_phone'))
                    ->orWhereIn('home_phone', $this->providerLogs->pluck('home_phone'))
                    ->orWhereIn('work_phone', $this->providerLogs->pluck('work_phone'));
            })
            ->groupBy("$label")
            ->get();

        $collection = $results->map(function (
                $item,
                $key
            ) use
            (
                $weightMultiplier,
                $label
            ) {
                return [
                    $label        => $item->{$label},
                    'total_count' => $item->total_count * $weightMultiplier,
                ];
            })
            ->reject(function ($item) use
            (
                $label
            ) {
                return !$item[$label];
            });

        if ($collection->isEmpty()) {
            $collection = new Collection();
        }

        return $collection;
    }

    protected function custodianLookup(
        $label,
        $weightMultiplier = 1
    ) {
        //return an empty collection if the lookup value is empty
        if (!$this->custodian) {
            return new Collection();
        }

        $results = DocumentLog::select(DB::raw("*, count('$label') as total_count"))
            ->where('ml_ignore', '=', false)
            ->where('custodian', '=', $this->custodian)
            ->whereNotNull("$label")
            ->orderBy('total_count', 'desc')
            ->groupBy("$label")
            ->get();

        $collection = $results->map(function (
            $item,
            $weightMultiplier
        ) use
        (
            $label
        ) {
            return [
                $label        => $item->{$label},
                'total_count' => $item->total_count * $weightMultiplier,
            ];
        })
            ->reject(function ($item) use
            (
                $label
            ) {
                return !$item[$label];
            });

        if ($collection->isEmpty()) {
            $collection = new Collection();
        }

        return $collection;
    }

    protected function providersLookup(
        $label,
        $weightMultiplier = 1
    ) {
        //return an empty collection if the lookup value is empty
        if (!$this->providerLogs) {
            return new Collection();
        }

        $results = ProviderLog::select(DB::raw("*, count('$label') as total_count"))
            ->where('ml_ignore', '=', false)
            ->whereIn('first_name', $this->providerLogs->pluck('first_name'))
            ->whereIn('last_name', $this->providerLogs->pluck('last_name'))
            ->whereNotNull("$label")
            ->orderBy('total_count', 'desc')
            ->groupBy("$label")
            ->get();

        $collection = $results->map(function ($item) use
        (
            $label,
            $weightMultiplier
        ) {
            return [
                $label        => $item->{$label},
                'total_count' => $item->total_count * $weightMultiplier,
            ];
        })
            ->reject(function ($item) use
            (
                $label
            ) {
                return !$item[$label];
            });

        if ($collection->isEmpty()) {
            $collection = new Collection();
        }

        return $collection;
    }
}