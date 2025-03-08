<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Town;
use Illuminate\Http\Request;

class Controller
{
    private function getCars(Request $request)
    {
        $brand = $request->input("brand");
        $model = $request->input("model");
        $cars = Car::where("brand","=",$brand)->where("model","=",$model);
        $min = $request->input("min");
        $max = $request->input("max");
        if ($min != '' && $max != ''){
            $cars = $cars->where("regdate",">=",$min)->where("regdate","<=",$max);
        }
        $cars = $cars->get();

        $nantes = Town::where("code_postal","=",env("POSTCODE","44000"))->first();
        foreach ($cars as $car)
        {
            if ($car->distance == 0)
            {
                $town = Town::where("code_postal","=",$car->postcode)->first();
                if ($town && $town->lat && $town->lng)
                {
                    $distance = $this->haversineDistance($nantes->lat, $nantes->lng, $town->lat, $town->lng);
                    $car->distance = (int) $distance;
                    $car->save();
                }
            }
        }

        return $cars;
    }

    public function json(Request $request)
    {
        $cars = $this->getCars($request);
        return response()->json($cars);
    }

    public function index()
    {
        $nb = count(Car::all());
        $brands = Car::groupby("brand")->distinct("brand")->orderby("brand")->get();
        $models = [];
        $dates = json_encode([]);
        $brand = "";
        $model = "";
        $min = "";
        $max = "";
        $regdates = [];
        $cars = [];
        return view('graph', compact("cars","nb", "regdates","min","max","dates","brand","model","brands","models"));
    }

    public function postCars(Request $request)
    {
        $nb = count(Car::all());
        $brand = $request->input("brand");
        $model = $request->input("model");
        $min = $request->input("min");
        $max = $request->input("max");
        $brands = Car::groupby("brand")->distinct("brand")->orderby("brand")->get();
        $models = Car::where("brand","=",$brand)->groupby("model")->distinct("model")->orderby("model")->get();
        $regdates = Car::where("brand","=",$brand)->where("model","=",$model)->groupby("regdate")->distinct("regdate")->orderby("regdate")->get();

        if ($min == '' && $max ==''){
            foreach ($regdates as $regdate) {
                if ($min == '') {
                    $min = $regdate->regdate;
                }
                $max = $regdate->regdate;
            }
        }

        $dates = [];
        foreach ($regdates as $regdate)
        {
            if ($regdate->regdate >= $min && $regdate->regdate <= $max){
                $dates[] = ["name" => $regdate->regdate, "id"=>$regdate->regdate];
            }
        }
        $dates = json_encode($dates);
        $cars = $this->getCars($request);
        return view('graph', compact("cars","nb","min","max","dates","regdates","brand","model","brands","models"));
    }

    public function save(Request $request)
    {
        $path = storage_path("app/private/".$request->file->storeAs('json', 'filename.json'));
        $items = json_decode(file_get_contents($path), true);
        $errors = [];
        foreach ($items as $item) {
            $dom = new \IvoPetkov\HTML5DOMDocument();

            try {
                $car = Car::where("url","=",$item['url'])->first();
                if (!$car) {
                    $car = new Car();
                }
                $car->url = $item['url'];
                $car->title = $item['title'];
                $car->brand = $item['brand'];
                $car->model = $item['model'];
                $car->postcode = $item['postcode'];
                $car->price = (int) str_replace("€","",str_replace("Prix:","",
                    str_replace(" ","", str_replace(" ","",$item['price']))));

                if (!isset($item['content']))
                {
                    $adParams = $item['adParams'];
                    $car->regdate = (int) str_replace("Année","",$adParams[0]);
                    $car->mileage = (int) str_replace("km","",str_replace("Kilométrage","",$adParams[1]));
                    $car->fuel = str_replace("Carburant","",$adParams[2]);
                    $car->gearbox = str_replace("Boîte de vitesse","",$adParams[3]);
                } else {
                    //Gros import avec tous les produits
                    $dom->loadHTML($item['content'], $dom::ALLOW_DUPLICATE_IDS);

                    foreach ($dom->querySelectorAll("p.opacity-dim-1") as $p) {
                        $p->parentNode->removeChild($p);
                    }

                    foreach ($dom->querySelectorAll("div") as $criteria) {
                        if ($criteria->getAttribute('data-test-id') == 'criteria') {
                            $criteriaName = $criteria->getAttribute('data-qa-id');
//                    echo $criteriaName."<br/>";
                            $criteriaName = str_replace('u_car_', '', $criteriaName);
                            $criteriaName = str_replace('criteria_item_', '', $criteriaName);

                            $value = $criteria->textContent;
                            if (in_array($criteriaName, ["vehicle_technical_inspection_a","critair","issuance_date","regdate", "mileage", "doors", "seats", "horsepower", "horse_power_din"])) {
                                $value = (int)$value;
                            }
                            $car->$criteriaName = $value;

                        }
                    }
                }


                $car->save();
            } catch(\Exception $e) {
                //Nothing
                $errors[] = $e->getMessage();
            }
        }

        $fp = fopen(storage_path("errors.log"), "w+");
        foreach ($errors as $error){
            fputs($fp, $error. PHP_EOL);
        }
        fclose($fp);

        return redirect('/graph');
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Rayon de la Terre en km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance en km
    }

    public function csv(Request $request)
    {
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=cars.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];

        $cars = $this->getCars($request);
        $list = $cars->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);
    }
}
