@extends('app.app_layout')
@section('title', 'Anomalies durant la saisie')

@section('content')


<section class="section">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Code-barre saisi avec la note mais matricule non saisi</h5>

                  <!-- Default Table -->
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Code-barre</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($anomalies[0] as $anomalie_m)
                        <tr>
                            <td style="color: red">{{ $anomalie_m->id_ue_ec_note }}-{{ $anomalie_m->numero_note }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <!-- End Default Table Example -->
                </div>
              </div>
            </div>
        </div>

        <div class = "col-lg-6">
            <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Code-barre saisi avec le matricule mais note non saisie</h5>

                  <!-- Default Table -->
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Code-barre</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($anomalies[1] as $anomalie_n)

                        <tr>
                            <td style="color:red">{{ $anomalie_n->id_ue_ec_matricule }}-{{ $anomalie_n->numero_matricule }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <!-- End Default Table Example -->
                </div>
              </div>
            </div>
        </div>
    </div>


</section>
@endsection
