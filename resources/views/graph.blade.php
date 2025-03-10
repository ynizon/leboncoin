<x-app-layout>
    <div class="row g-2">
        <form method="post" action="/save"  enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-md-6 my-3">
                <h4>{{count($cars)}}/{{$nb}} voitures en base
                <br/>(distance calculée depuis {{env("POSTCODE")}})</h4>
            </div>
            <div class="col-md-6">
                <input type="file" name="file" style="display:inline"/>
                <input type="submit" value="Uploader un fichier leboncoin" class="btn btn-primary"/>
            </div>
        </form>
    </div>
    <div class="row g-2">
        <hr/>
    </div>
    <div class="row g-2">
        <form method="post" action="/graph">
            {{csrf_field()}}
            <div class="col-md-3">
                <select name="brand" class="form-control" onchange="submit()">
                    <option value=""></option>
                    @foreach ($brands as $brandtmp)
                        <option value="{{$brandtmp->brand}}" @if ($brand==$brandtmp->brand) selected @endif>{{$brandtmp->brand}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="model" class="form-control" onchange="submit()">
                    <option value=""></option>
                    @foreach ($models as $modeltmp)
                        <option value="{{$modeltmp->model}}" @if ($model==$modeltmp->model) selected @endif>{{$modeltmp->model}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="min" class="form-control" onchange="submit()">
                    <option value=""></option>
                    @foreach ($regdates as $regdatetmp)
                        <option value="{{$regdatetmp->regdate}}" @if ($min==$regdatetmp->regdate) selected @endif>{{$regdatetmp->regdate}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="max" class="form-control" onchange="submit()">
                    <option value=""></option>
                    @foreach ($regdates as $regdatetmp)
                        <option value="{{$regdatetmp->regdate}}" @if ($max==$regdatetmp->regdate) selected @endif>{{$regdatetmp->regdate}}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <hr style="clear:both"/>
    <div class="">
        <main class="">
            <div id="container"></div>
            <hr/>
            <a alt="Télécharger le CSS" href="/csv/?brand={{$brand}}&model={{$model}}&min={{$min}}&max={{$max}}"><i class="fa-solid fa-download"></i></a>
            <table class="table" id="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Prix</th>
                        <th>Km</th>
                        <th>Distance</th>
                        <th>Année</th>
                        <th>Marque</th>
                        <th>Modele</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cars as $car)
                        <tr>
                            <td>
                                <a target="_blank" href="{{$car->url}}">{{$car->title}}</a>
                            </td>
                            <td nowrap style="background:{{$prices[$car->price]}}">
                                {{$car->price}}
                            </td>
                            <td nowrap style="background:{{$kms[$car->mileage]}}">
                                {{$car->mileage}}
                            </td>
                            <td nowrap style="background:{{$distances[$car->distance]}}">
                                {{$car->distance}}
                            </td>
                            <td>
                                {{$car->regdate}}
                            </td>
                            <td>
                                {{$car->brand}}
                            </td>
                            <td>
                                {{$car->model}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <script>
                function interpolateColor(color1, color2, factor) {
                    let result = color1.slice();
                    for (let i = 0; i < 3; i++) {
                        result[i] = Math.round(result[i] + factor * (color2[i] - color1[i]));
                    }
                    return result;
                }

                function generateGradientColors(numColors) {
                    let colors = [];
                    let red = [139, 0, 0];       // Rouge foncé
                    let orange = [255, 140, 0];   // Orange
                    let green = [144, 238, 144];  // Vert clair

                    if (numColors < 2) return [`rgba(${red[0]},${red[1]},${red[2]},0.5)`];

                    let steps1 = Math.floor(numColors / 2); // Dégradé rouge → orange
                    let steps2 = numColors - steps1;        // Dégradé orange → vert

                    // Interpolation du rouge vers l'orange
                    for (let i = 0; i < steps1; i++) {
                        let factor = i / (steps1 - 1);
                        let color = interpolateColor(red, orange, factor);
                        colors.push(`rgba(${color[0]},${color[1]},${color[2]},0.5)`);
                    }

                    // Interpolation de l'orange vers le vert
                    for (let i = 0; i < steps2; i++) {
                        let factor = i / (steps2 - 1);
                        let color = interpolateColor(orange, green, factor);
                        colors.push(`rgba(${color[0]},${color[1]},${color[2]},0.5)`);
                    }

                    return colors;
                }

                // Exemple d'utilisation avec un nombre variable de couleurs
                const numColors = {{count($regdates)}}; // Change cette valeur pour tester
                const gradientColors = generateGradientColors(numColors);

                //console.log(gradientColors); // Affiche la liste des couleurs

                // Appliquer dynamiquement à Highcharts
                Highcharts.setOptions({
                    colors: gradientColors
                });


                const series = {!! $dates !!}

                async function getData() {
                    const response = await fetch(
                        '/json/?brand={{$brand}}&model={{$model}}&min={{$min}}&max={{$max}}'
                    );
                    return response.json();
                }


                getData().then(data => {
                        const getData = regdate => {
                            return data
                                .filter(elm => elm.regdate === regdate)
                                .map(elm => ({
                                    x: elm.mileage,
                                    y: elm.price,
                                    url: elm.url,
                                    regdate: elm.regdate,
                                    distance: elm.distance,
                                    title: elm.title,
                                }));
                        };
                        series.forEach(s => {
                            s.data = getData(s.id);
                        });

                        Highcharts.chart('container', {
                            chart: {
                                type: 'scatter',
                                zooming: {
                                    type: 'xy'
                                }
                            },
                            title: {
                                text: 'Voitures '
                            },
                            subtitle: {
                                text:
                                    'Source: <a href="https://www.leboncoin.fr" target="_blank">Le boncoin</a>'
                            },
                            xAxis: {
                                title: {
                                    text: 'Kilométrage'
                                },
                                labels: {
                                    format: '{value} km'
                                },
                                startOnTick: true,
                                endOnTick: true,
                                showLastLabel: true
                            },
                            yAxis: {
                                title: {
                                    text: 'Prix'
                                },
                                labels: {
                                    format: '{value} €'
                                },

                            },
                            legend: {
                                enabled: true
                            },
                            plotOptions: {
                                scatter: {
                                    marker: {
                                        radius: 6,
                                        symbol: 'circle',
                                        states: {
                                            hover: {
                                                enabled: true,
                                                lineColor: 'rgb(100,100,100)'
                                            }
                                        }
                                    },
                                    states: {
                                        hover: {
                                            marker: {
                                                enabled: false
                                            }
                                        }
                                    },
                                    jitter: {
                                        x: 0.005
                                    }
                                }
                            },
                            tooltip: {
                                useHTML: true,
                                style: {
                                    pointerEvents: 'auto'
                                },
                                formatter: function () {
                                    return `Km: ${this.x} <br/>
                                    Prix: ${this.y} € <br/>
                                    Année: ${this.point.regdate} <br/>
                                    Distance: ${this.point.distance} <br/>
                                    <a href="${this.point.url}" target="_blank">${this.point.title}</a>`;
                                }
                            },
                            series
                        });
                    }
                );

                new DataTable('#table', {
                    "language": {
                        "sProcessing": "Traitement en cours...",
                        "sSearch": "Rechercher :",
                        "sLengthMenu": "Afficher _MENU_ éléments",
                        "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                        "sInfoEmpty": "Affichage de 0 à 0 sur 0 éléments",
                        "sInfoFiltered": "(filtré de _MAX_ éléments au total)",
                        "sLoadingRecords": "Chargement en cours...",
                        "sZeroRecords": "Aucun élément à afficher",
                        "sEmptyTable": "Aucune donnée disponible dans le tableau",
                        "oPaginate": {
                            "sFirst": "Premier",
                            "sPrevious": "Précédent",
                            "sNext": "Suivant",
                            "sLast": "Dernier"
                        },
                        "oAria": {
                            "sSortAscending": ": activer pour trier la colonne par ordre croissant",
                            "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
                        }
                    },
                    "order": [],
                    "paging": false,
                    "columnDefs": [
                        { "orderable": true, "targets": [1, 2, 3, 4] }, // Index 1 = 2ème colonne, Index 2 = 3ème colonne
                        { "orderable": false, "targets": "_all" } // Désactiver le tri sur les autres colonnes
                    ]
                });
            </script>
        </main>
    </div>
</x-app-layout>
