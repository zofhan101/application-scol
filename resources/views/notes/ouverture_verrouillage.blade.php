@extends('app.app_layout')
@section('title', 'Mise à jour des informations étudiant')

@section('content')


<section class="section">
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

    @else
        <div class="row">
            <div class="col-lg-4 col-md-4">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">Choix de l'examen</h5>
                        <select class="form-select" name="evaluation" id="evaluation">
                            @foreach($examens as $examen)
                                <option value="{{$examen->id_examen_par_au }}">{{ $examen->nom_session_examen }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4">
            </div>

            <div class="col-lg-4 col-md-4">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">Opération à effectuer</h5>
                        <select class="form-select" name="operation" id="operation">
                            @foreach($operations as $operation)
                                <option value="{{$operation->id_operation_sur_resultats}}">{{ $niveau->nom_operation_sur_resultats }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

    @endif
</section>

@endsection
