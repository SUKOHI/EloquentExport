<?php

namespace Sukohi\EloquentExport;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EloquentExportServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        function getExtension($filename) {

            return pathinfo($filename, PATHINFO_EXTENSION);

        };

        function getRowData($item, $rendering_values) {

            $row_data = [];

            if(empty($rendering_values)) {

                $row_data = array_values($item->toArray());

            } else {

                foreach($rendering_values as $rendering_value) {

                    if(is_callable($rendering_value)) {

                        $row_data[] = $rendering_value($item);

                    } else if(is_string($rendering_value)) {

                        $keys = explode('.', $rendering_value);
                        $item_value = $item;

                        foreach($keys as $key) {

                            $item_value = $item_value[$key];

                        }

                        $row_data[] = $item_value;

                    } else {

                        $row_data[] = '';

                    }

                }

            }

            return $row_data;

        }

        Collection::macro('export', function($filename = '', $options = []) {

            $extension = getExtension($filename);
            $render = Arr::get($options, 'render', []);
            $prepend = Arr::get($options, 'prepend', []);
            $append = Arr::get($options, 'append', []);

            if($extension === 'csv') {

                $encoding = Arr::get($options, 'encoding', 'UTF-8');
                $fluent = \FluentCsv::setEncoding($encoding);

                if(is_array($prepend)) {

                    foreach($prepend as $prepending_row) {

                        $fluent->addData($prepending_row);

                    }

                }

                $this->each(function($item) use($fluent, $render) {

                    $row_data = getRowData($item, $render);
                    $fluent->addData($row_data);

                });

                if(is_array($append)) {

                    foreach($append as $appending_row) {

                        $fluent->addData($appending_row);

                    }

                }

                return $fluent->download($filename);

            } else if(in_array($extension, ['xls', 'xlsx'])) {

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $excel_data = [];

                if(is_array($prepend)) {

                    foreach($prepend as $prepending_row) {

                        $excel_data[] = $prepending_row;

                    }

                }

                $this->each(function($item) use(&$excel_data, $render) {

                    $excel_data[] = getRowData($item, $render);

                });

                if(is_array($append)) {

                    foreach($append as $appending_row) {

                        $excel_data[] = $appending_row;

                    }

                }

                $sheet->fromArray($excel_data, null, 'A1');

                $callback = function() use($spreadsheet) {

                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');

                };
                $status = 200;
                $headers = [
                    'Content-Type' => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'attachment;filename="'. $filename .'"',
                    'Cache-Control' => 'max-age=0',
                ];
                return new StreamedResponse($callback, $status, $headers);

            } else {

                throw new \Exception('The extension must be one of the following: ".csv", ".xls", ".xlsx"');

            }

        });
    }

}
