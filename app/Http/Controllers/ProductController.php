<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): View
    {
        return $this->stub('products.index');
    }

    /**
     * The pumps/valves/control/water routes are registered as literal
     * segments ahead of {slug} in routes/web.php, so this action only ever
     * receives real product slugs — never the 4 reserved category words.
     */
    public function show(Request $request): View
    {
        return $this->stub('products.show', ['slug' => $this->routeParam($request, 'slug')]);
    }

    public function pumps(): View
    {
        return $this->stub('products.pumps');
    }

    public function valves(): View
    {
        return $this->stub('products.valves');
    }

    public function control(): View
    {
        return $this->stub('products.control');
    }

    public function water(): View
    {
        return $this->stub('products.water');
    }
}
