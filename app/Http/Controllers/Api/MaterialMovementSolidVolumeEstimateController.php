<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialMovementSolidVolumeEstimateRequest;
use App\Http\Requests\UpdateMaterialMovementSolidVolumeEstimateRequest;
use App\Http\Resources\MaterialMovementSolidVolumeEstimateResource;
use App\Interfaces\MaterialMovementSolidVolumeEstimateRepositoryInterface;
use App\Models\Station;
use Illuminate\Http\Request;

class MaterialMovementSolidVolumeEstimateController extends Controller
{
    protected $MaterialMovementSolidVolumeEstimateRepository;

    public function __construct(MaterialMovementSolidVolumeEstimateRepositoryInterface $MaterialMovementSolidVolumeEstimateRepository)
    {
        $this->MaterialMovementSolidVolumeEstimateRepository = $MaterialMovementSolidVolumeEstimateRepository;
    }

    public function index(Request $request)
    {
        try {
            $materialMovementSolidVolumeEstimates = $this->MaterialMovementSolidVolumeEstimateRepository->getAllMaterialMovementSolidVolumeEstimates($request->all());

            return ResponseHelper::jsonResponse(true, 'Success', MaterialMovementSolidVolumeEstimateResource::collection($materialMovementSolidVolumeEstimates), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function store(StoreMaterialMovementSolidVolumeEstimateRequest $request)
    {
        $request = $request->validated();

        $code = $request['code'];
        if ($code == 'AUTO') {
            $tryCount = 0;
            do {
                $code = $this->MaterialMovementSolidVolumeEstimateRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $this->MaterialMovementSolidVolumeEstimateRepository->isUniqueCode($code));
            $request['code'] = $code;
        }

        $station = Station::find($request['station_id']);
        if ($station->is_active == false) {
            return ResponseHelper::jsonResponse(false, 'Station tidak aktif.', null, 405);
        }

        try {
            $materialMovementSolidVolumeEstimate = $this->MaterialMovementSolidVolumeEstimateRepository->create($request);

            return ResponseHelper::jsonResponse(true, 'Volume estimasi padat berhasil ditambahkan', new MaterialMovementSolidVolumeEstimateResource($materialMovementSolidVolumeEstimate), 201);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $materialMovementSolidVolumeEstimate = $this->MaterialMovementSolidVolumeEstimateRepository->getMaterialMovementSolidVolumeEstimateById($id);

            if (! $materialMovementSolidVolumeEstimate) {
                return ResponseHelper::jsonResponse(false, 'Volume estimasi padat tidak ditemukan', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Success', new MaterialMovementSolidVolumeEstimateResource($materialMovementSolidVolumeEstimate), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function update(UpdateMaterialMovementSolidVolumeEstimateRequest $request, $id)
    {
        $request = $request->validated();

        $materialMovementSolidVolumeEstimate = $this->MaterialMovementSolidVolumeEstimateRepository->getMaterialMovementSolidVolumeEstimateById($id);

        if (! $materialMovementSolidVolumeEstimate) {
            return ResponseHelper::jsonResponse(false, 'Volume estimasi padat tidak ditemukan', null, 404);
        }

        $code = $request['code'];
        if ($code == 'AUTO') {
            $tryCount = 0;
            do {
                $code = $this->MaterialMovementSolidVolumeEstimateRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $this->MaterialMovementSolidVolumeEstimateRepository->isUniqueCode($code, $id));
            $request['code'] = $code;
        }

        $station = Station::find($request['station_id']);
        if ($station->is_active == false) {
            return ResponseHelper::jsonResponse(false, 'Station tidak aktif.', null, 405);
        }

        try {
            $materialMovementSolidVolumeEstimate = $this->MaterialMovementSolidVolumeEstimateRepository->update($request, $id);

            return ResponseHelper::jsonResponse(true, 'Volume estimasi padat berhasil diubah', new MaterialMovementSolidVolumeEstimateResource($materialMovementSolidVolumeEstimate), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $materialMovementSolidVolumeEstimate = $this->MaterialMovementSolidVolumeEstimateRepository->delete($id);

            return ResponseHelper::jsonResponse(true, 'Volume estimasi padat berhasil dihapus', new MaterialMovementSolidVolumeEstimateResource($materialMovementSolidVolumeEstimate), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
