<?php

namespace Modules\Recruit\DataTables;

use App\DataTables\BaseDataTable;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Recruit\Entities\RecruitApplicationStatus;
use Modules\Recruit\Entities\RecruitJobApplication;
use Yajra\DataTables\Facades\DataTables;

class DashboardPipelineWidgetDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatable = Datatables::of($query);
        $statusColumns = RecruitApplicationStatus::all();
        $columns = $statusColumns->toArray();
        $datatable->addColumn('title', function ($row) {
            $totalApplicationCount = RecruitJobApplication::where('recruit_job_id', $row->id)->count();

            return '<div class="media-bod1y column-width-title"><p class="mb-0">' . $row->title . '</p><p class="mb-0 f-12 text-dark-grey">' . __('recruit::modules.jobApplication.totalApplications') . ' ' . '-' . ' ' . $totalApplicationCount . '</p></div>';
        });

        foreach ($columns as $column) {
            $datatable->addColumn($column['slug'], function ($row) use ($column) {
                $colorCode = RecruitApplicationStatus::where('slug', $column['slug'])->first();

                $totalCount = $row->applications->filter(function ($value, $key) use ($column) {
                    return $value->recruit_application_status_id == $column['id'];
                })->count();

                if ($totalCount == 1) {
                    return '<span class="d-flex justify-content-center badge badge-pill text-white border-1 badge-light column-width" style=\'background-color: ' . $colorCode->color . '\'><div class="d-inline-block mr-1"></div>' . $totalCount . ' ' . __('recruit::modules.interviewSchedule.candidate') . '</span>';
                }
                elseif ($totalCount > 0) {
                    return '<span class="d-flex justify-content-center badge badge-pill text-white border-1 badge-light column-width" style=\'background-color: ' . $colorCode->color . '\'><div class="d-inline-block mr-1"></div>' . $totalCount . ' ' . __('recruit::modules.jobApplication.candidates') . '</span>';
                }
                else {
                    return '<span class="d-flex justify-content-center badge badge-pill border-1 badge-light column-width" style=\'height: 11mm\'><div class="d-inline-block mr-1"></div>' . ' ' . '</span>';
                }
            });
        }

        $datatable->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id);

        $rawColumns = $statusColumns->pluck('slug')->toArray();
        $rawColumns[] = array_push($rawColumns, 'title');
        $datatable->rawColumns($rawColumns)->make(true);

        return $datatable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(RecruitJob $model)
    {
        $model = RecruitJob::with('applications')->where('status', 'open');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return parent::setBuilder('pipeline-widget-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["pipeline-widget-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                   //
                   $(".select-picker").selectpicker();
                }'

            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = RecruitApplicationStatus::all();
        $newColumns = [];
        $newColumns[__('recruit::modules.jobApplication.jobs')] = ['data' => 'title', 'name' => 'title', 'orderable' => true, 'searchable' => false, 'visible' => true, 'title' => __('recruit::modules.jobApplication.jobs')];

        foreach ($columns as $column) {
            $newColumns[$column->status] = ['data' => $column->slug, 'name' => $column->slug, 'orderable' => false, 'searchable' => false, 'visible' => true, 'title' => __('recruit::app.jobApplication.'.str($column->slug)->camel())];
        }

        return $newColumns;
    }

}
