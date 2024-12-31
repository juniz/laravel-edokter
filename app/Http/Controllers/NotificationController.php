<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotificationsData(Request $request)
    {
        // For the sake of simplicity, assume we have a variable called
        // $notifications with the unread notifications. Each notification
        // have the next properties:
        // icon: An icon for the notification.
        // text: A text for the notification.
        // time: The time since notification was created on the server.
        // At next, we define a hardcoded variable with the explained format,
        // but you can assume this data comes from a database query.

        try {
            $data = \App\Models\KonsultasiMedik::query()
                // ->join('reg_periksa', 'konsultasi_medik.no_rawat', '=', 'reg_periksa.no_rawat')
                // ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->leftJoin('jawaban_konsultasi_medik', 'konsultasi_medik.no_permintaan', '=', 'jawaban_konsultasi_medik.no_permintaan')
                ->where('kd_dokter_dikonsuli', session()->get('username'))
                ->where('jawaban_konsultasi_medik.diagnosa_kerja', '=', null)
                ->orderBy('konsultasi_medik.tanggal', 'desc')
                ->select('konsultasi_medik.no_permintaan')
                ->limit(5)
                ->get();

            $notifications = [];
            foreach ($data as $d) {
                $notifications[] = [
                    'icon' => 'fas fa-fw fa-envelope',
                    'text' => $d->no_permintaan . ' -  Konsultasi Medik',
                    'url' => '/konsultasi/jawaban/' . $d->no_permintaan,
                    'time' => rand(0, 10) . ' minutes',
                ];
            }
            // $notifications = [
            //     [
            //         'icon' => 'fas fa-fw fa-envelope',
            //         'text' => $data->no_permintaan . ' -  Konsultasi Medik',
            //         // 'time' => rand(0, 10) . ' minutes',
            //     ],
            // ];

            // Now, we create the notification dropdown main content.

            $dropdownHtml = '';

            foreach ($notifications as $key => $not) {
                $icon = "<i class='mr-2 {$not['icon']}'></i>";
                $url = $not['url'];

                // $time = "<span class='float-right text-muted text-sm'>
                //            {$not['time']}
                //          </span>";

                $dropdownHtml .= "<a href='{$url}' class='dropdown-item'>
                                    {$icon}{$not['text']}
                                  </a>";

                if ($key < count($notifications) - 1) {
                    $dropdownHtml .= "<div class='dropdown-divider'></div>";
                }
            }

            // Return the new notification data.

            return [
                'label' => count($notifications),
                'label_color' => 'danger',
                'icon_color' => 'dark',
                'dropdown' => $dropdownHtml,
            ];
        } catch (\Exception $e) {
            return [
                'label' => 0,
                'label_color' => 'danger',
                'icon_color' => 'dark',
                'error' => $e->getMessage(),
                'dropdown' => '',
            ];
        }
    }
}
