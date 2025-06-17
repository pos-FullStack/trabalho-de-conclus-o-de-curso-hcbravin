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
});