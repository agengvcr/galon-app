<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatatableHelper
{
    public static function getServerSideProcessingData(Request $request, $table, $columns, $whereConditions = [])
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column'];
        $columnName = $columnName_arr[$columnIndex]['data'];
        $columnSortOrder = $order_arr[0]['dir'];
        $searchValue = $search_arr['value'];

        // Total records
        $totalRecordsQuery = DB::table($table);
        foreach ($whereConditions as $condition) {
            $totalRecordsQuery->where($condition[0], $condition[1], $condition[2]);
        }
        $totalRecords = $totalRecordsQuery->count();

        // Total records with filter
        $totalRecordswithFilterQuery = DB::table($table);
        foreach ($whereConditions as $condition) {
            $totalRecordswithFilterQuery->where($condition[0], $condition[1], $condition[2]);
        }
        $totalRecordswithFilterQuery->where(function ($query) use ($columns, $searchValue) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%' . $searchValue . '%');
            }
        });
        $totalRecordswithFilter = $totalRecordswithFilterQuery->count();

        // Fetch records
        $recordsQuery = DB::table($table);
        foreach ($whereConditions as $condition) {
            $recordsQuery->where($condition[0], $condition[1], $condition[2]);
        }
        $recordsQuery->where(function ($query) use ($columns, $searchValue) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%' . $searchValue . '%');
            }
        });
        $records = $recordsQuery->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $data_arr[] = $record;
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        return $response;
    }
}
