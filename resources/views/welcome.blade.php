<x-app-layout>
    <form method="post" action="/save"  enctype="multipart/form-data">

        <div class="">
            <h1>{{$nb}} voitures en base<br/></h1>
            <a href="/graph">Voir les statistiques</a>
            <br/><br/>
            <p class="">
                {{csrf_field()}}
                Rajouter un fichier leboncoin:
                <input type="file" name="file" /><br/>
                <input type="submit" value="Upload" class="btn btn-primary"/>
            </p>
        </div>
    </form>
</x-app-layout>
