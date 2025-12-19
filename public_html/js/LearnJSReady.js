$(function(){
    if(parseInt($('[data-toggle="tooltip"]').length)>0){
        $('[data-toggle="tooltip"]').tooltip(); setTimeout(function(){$('[data-toggle="tooltip"]').tooltip();},1500);
    }

    $('[data-bs-nome]').ready(function(){
        $(this).attr('data-bs-nome-main',$(this).text().trim());
    
    }).click(function(){
        let nomes = $(this).attr("data-bs-nome").split(" "); // Divide os nomes
        let nomeAtual = $(this).text().trim(); // Pega o nome atual do botão
        $(this).text((nomeAtual === nomes[0] ? nomes[1] : nomes[0]));
    });

    $('[data-bs-cor]').click(function(){
        $(this).toggleClass($(this).attr('data-bs-cor'));
    })

    $('[data-mask]').each(function() {
        var maskType = $(this).data('mask');
        var input = $(this);

        switch (maskType) {
            case 'cep':
                input.mask('00000-000');
                break;

            case 'cpf':
                input.mask('000.000.000-00');
                break;

            case 'telefone':
                input.mask('(00) 00000-0000');
                break;

            case 'data':
                input.mask('00/00/0000');
                break;

            default:
                console.warn('Máscara não definida para:', maskType);
        }
    });

    if($('[data-autobg="true"]').length){
        $('#page-content-wrapper').css("background-image", "url('/images/background-poly.jpg')");
    }
});