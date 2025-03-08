<?php

namespace Database\Seeders;

use App\Models\Town;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $csvFileName = "base-officielle-codes-postaux.csv";
        $csvFile = public_path('../database/seeders/' . $csvFileName);
        $cities = $this->readCSV($csvFile,array('delimiter' => ','));

        $nb = 0;
        foreach ($cities as $city)
        {
            if ($nb > 0)
            {
                $town = new Town();
                $town->code_commune_insee = $city[0];
                $town->nom_de_la_commune = $city[1];
                $town->code_postal = $city[2];
                $town->libelle_d_acheminement = $city[3];
                $town->ligne_5 = $city[4];
                $coords = explode(",",$city[5]);
                if (isset($coords[1]))
                {
                    $town->lat = $coords[0];
                    $town->lng = $coords[1];
                }
                $town->save();
            }
            $nb++;
        }
    }

    public function readCSV($csvFile, $delimiter = ',')
    {
        $file_handle = fopen($csvFile, 'r');
        while ($csvRow = fgetcsv($file_handle)) {
            $line_of_text[] = $csvRow;
        }
        fclose($file_handle);
        return $line_of_text;
    }
}
