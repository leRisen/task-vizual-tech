<?php

namespace App\Http\Controllers;

use Exception;
use DataTables;

use App\Models\Product;

use Illuminate\Contracts\View\{
    View, Factory
};

use Yajra\DataTables\Html\Builder;
use Illuminate\Contracts\Foundation\Application;

class ProductController extends Controller
{
    /**
     * @var Builder
     */
    private $datatablesBuilder;

    public function __construct(Builder $datatablesBuilder)
    {
        $this->datatablesBuilder = $datatablesBuilder;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     * @throws Exception
     */
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Product::query()->with('attributes'))->toJson();
        }

        $html = $this->datatablesBuilder->columns([
            ['data' => 'id', 'name' => 'id', 'title' => 'Id', 'searchable' => false],
            ['data' => 'name', 'name' => 'name', 'title' => 'Name', 'searchable' => true, 'orderable' => false],
            ['data' => 'price', 'name' => 'price', 'title' => 'Price', 'searchable' => false],
        ]);

        return view('products.index', compact('html'));
    }
}
