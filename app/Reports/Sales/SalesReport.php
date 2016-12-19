<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:16 PM
 */

namespace App\Reports\Sales;


interface SalesReport
{

    public function generateData();

    public function renderPDF();

}