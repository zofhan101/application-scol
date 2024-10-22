@extends('app.app_layout')
@section('title', 'Interface de saisie des notes')

@section('content')
<div class="pagetitle">
    <h1>Saisie des notes d'examen</h1>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Veuillez scanner le code-barres sur la copie d'examen puis saisir la note ci-dessous

      </div>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body" style="padding-top: 2%">
                  <form class="row g-3" action="#" id="form">
                    <div class="col-12">
                        <input type="hidden" id="barcode" name="barcode">

                        <label for="inputAddress" class="form-label">Note obtenue</label>
                        <input type="text" class="form-control" name="note" id="note" disabled>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="w-100 btn btn-primary" id="submit_button" disabled>Enregistrer</button>
                    </div>
                  </form>


                </div>
              </div>


        </div>
    </div>
</section>
<script>
    let code_input = document.getElementById('barcode');
    let note_input = document.getElementById('note');
    let form = document.getElementById('form');
    let submit_button = document.getElementById('submit_button');
    let barcode="";
    let formdata;

    document.addEventListener('keydown', function(event){
        if (event.key === 'Enter') {
            console.log("Code-barres scanné :", barcode);
            code_input.value = barcode;
            note_input.disabled = false;
            submit_button.disabled = false;
            //note_input.focus();
            barcode = "";
        } else {
            barcode += event.key;
        }
    });

    form.addEventListener('submit', function(event){
        event.preventDefault();
        formdata = new FormData(this);
        enregistrer_donnees(formdata);
        note_input.value = "";
        note_input.disabled = true;
        submit_button.disabled = true;
        barcode = "";
    });

    function enregistrer_donnees(formdata){
        let url = "{{ route('enregistrer_note') }}";
        try {
            let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json, text/html',
                'Cookie': document.cookie
            },
            credentials: 'include',
            body: formdata
            });

            let data = await response.json();
            if(response.ok){
                console.log('Enregistrement note :reponse ok ', data);

            }else {
                console.error('Enregistrement note :reponse pas ok ', data);
                barcode = "";
                alert('Erreur lors de l\'enregistrement: ', data, 'Veuillez réessayer');

            }
        } catch (error) {
            console.error('Enregistrement note :reponse pas ok ',error);
            barcode ="";
            alert('Erreur lors de l\'enregistrement: ', error, 'Veuillez réessayer');
        }

    }




</script>

@endsection

