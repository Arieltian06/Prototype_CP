<?php

namespace App\Services;

use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use DomainException;

class RecommendationService
{
    public function __construct(private DistanceService $distance, private SawService $saw) {}

    public function recommend(JenjangPendidikan $jenjang, MataPelajaran $mapel, Sekolah $sekolah, ?array $origin = null): array
    {
        if ((int) $mapel->id_jenjang !== (int) $jenjang->getKey()
            || (int) $sekolah->id_jenjang !== (int) $jenjang->getKey()) {
            throw new DomainException('Mata pelajaran dan sekolah harus sesuai dengan jenjang yang dipilih.');
        }

        $origin ??= ['latitude' => $sekolah->latitude, 'longitude' => $sekolah->longitude, 'label' => $sekolah->nama_sekolah];
        if (! $this->validCoordinates($origin['latitude'] ?? null, $origin['longitude'] ?? null)) {
            throw new DomainException('Koordinat lokasi asal belum valid. Pilih sekolah lain atau gunakan lokasi perangkat.');
        }
        $origin = [
            'latitude' => (float) $origin['latitude'],
            'longitude' => (float) $origin['longitude'],
            'label' => $origin['label'] ?? 'Lokasi perangkat',
        ];

        $criteria = Kriteria::orderBy('kode_kriteria')->get();
        // Validate the configuration even when no candidate places exist.
        $this->saw->calculate([], $criteria->toArray());
        foreach ($criteria as $criterion) {
            if (! in_array($criterion->sumber_nilai, ['jenjang', 'mapel', 'jarak', 'manual'], true)) {
                throw new DomainException("Sumber nilai kriteria {$criterion->kode_kriteria} belum diatur dengan benar.");
            }
        }

        $places = TempatBelajar::where('status', 'aktif')
            ->whereHas('jenjang', fn ($query) => $query->where('jenjang_pendidikan.id_jenjang', $jenjang->getKey()))
            ->whereHas('mapel', fn ($query) => $query->where('mata_pelajaran.id_mapel', $mapel->getKey()))
            ->with(['jenjang', 'mapel', 'penilaian'])->orderBy('id_tempat')->get();

        $alternatives = [];
        $skipped = [];
        foreach ($places as $place) {
            if (! $this->validCoordinates($place->latitude, $place->longitude)) {
                $skipped[] = ['id' => $place->getKey(), 'nama' => $place->nama_tempat, 'reason' => 'Koordinat tempat belum valid.'];

                continue;
            }

            $levelScore = (float) $place->jenjang->firstWhere('id_jenjang', $jenjang->getKey())->pivot->nilai_kesesuaian;
            $subjectScore = (float) $place->mapel->firstWhere('id_mapel', $mapel->getKey())->pivot->nilai_kesesuaian;
            $distance = $this->distance->calculateDistance($origin['latitude'], $origin['longitude'], $place->latitude, $place->longitude);
            $values = [];
            $missing = [];
            foreach ($criteria as $criterion) {
                $value = match ($criterion->sumber_nilai) {
                    'jenjang' => $levelScore,
                    'mapel' => $subjectScore,
                    'jarak' => $distance,
                    'manual' => $place->penilaian->firstWhere('id_kriteria', $criterion->getKey())?->nilai,
                };
                if ($value === null || ! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0) {
                    $missing[] = $criterion->kode_kriteria;
                } else {
                    $values[$criterion->kode_kriteria] = (float) $value;
                }
            }
            if ($missing !== []) {
                $skipped[] = [
                    'id' => $place->getKey(), 'nama' => $place->nama_tempat,
                    'reason' => 'Nilai kriteria '.implode(', ', $missing).' belum diisi atau belum valid.',
                ];

                continue;
            }

            $alternatives[] = [
                'id' => $place->getKey(),
                'nama' => $place->nama_tempat,
                'alamat' => $place->alamat,
                'foto_url' => $place->foto_url,
                'jarak' => $distance,
                'kesesuaian_jenjang' => $levelScore,
                'kesesuaian_mapel' => $subjectScore,
                'karakteristik' => $place->karakteristik,
                'latitude' => $place->latitude,
                'longitude' => $place->longitude,
                'is_prototype' => $place->is_prototype,
                'mapel' => $place->mapel->pluck('nama_mapel')->unique()->values()->all(),
                'reason' => sprintf(
                    'Memiliki nilai kesesuaian jenjang %s sebesar %s dan mata pelajaran %s sebesar %s; berjarak lurus sekitar %s km dari lokasi asal. Peringkat mengikuti kriteria dan bobot yang digunakan.',
                    $jenjang->nama_jenjang, $levelScore, $mapel->nama_mapel, $subjectScore, number_format($distance, 2, ',', '.')
                ),
                'values' => $values,
            ];
        }

        return array_merge($this->saw->calculate($alternatives, $criteria->toArray()), [
            'origin' => $origin,
            'skipped_count' => count($skipped),
            'skipped_reasons' => $skipped,
        ]);
    }

    private function validCoordinates(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && is_finite((float) $latitude) && is_finite((float) $longitude)
            && (float) $latitude >= -90 && (float) $latitude <= 90
            && (float) $longitude >= -180 && (float) $longitude <= 180;
    }
}
