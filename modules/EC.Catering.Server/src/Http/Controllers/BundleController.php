<?php
namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Bundle\Repository\BundleRepository;

class BundleController extends Controller
{
    private $bundle;

    public function __construct(
        BundleRepository $bundleRepository)
    {
        $this->bundle = $bundleRepository;
    }

    public function test()
    {
    }

}