(function($){
    $.fn.extend({
        pulsar: function (n = 2, delay = 200) {
            // Caso queira que o efeito seja repetido infinitamente, basta colocar o valor de n como 0
             if (n === 0) {
                const $el = this; // guarda a referência do jQuery
                const ciclo = delay * 2; // tempo total de um pulso (ida + volta)

                const animar = function () {
                    $el.fadeTo(delay, 0.3).fadeTo(delay, 1.0);
                };

                animar(); // executa imediatamente o primeiro pulso
                const intervalo = setInterval(animar, ciclo + 50); // leve folga entre ciclos

                // Se quiser poder parar depois, guarda o id no próprio elemento:
                $el.data('pulsarInterval', intervalo);
                return this;
            }

            // Caso queira que o efeito seja repetido, basta colocar o valor de n como 0
            for(i = 1; i <= n; i++) {
                this.fadeTo(delay, 0.3).fadeTo(delay, 1.0);
            }
            return;
        }
    });
})(jQuery);

$(function () {

    $('#MainNotificacoesAccount').pulsar(0, 1000);

    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    // Share
    $('.ShareText').click(function () {
        const texto = $($(this).attr('data-eb-target')).val();
        const titulo = $(this).attr('data-eb-title');
        if (navigator.share) {
            navigator.share({
                title: titulo,
                text: texto
            });
        } else {
            navigator.clipboard.writeText(texto);
            $(this).html('<i class="bi bi-clipboard me-1"></i> Copiado').toggleClass('btn-warning btn-success');
        }
    });

    // Criar uma nova agência
    // Mascara de CEP
    $('#agencia-cep').mask('00000-000');

    // Toggle visual para o estado de trancado/aberto da agência ao criar uma nova agencia
    $('#trancado-toggle').change(function () {
        $('#card-trancado-on, #card-trancado-off')
            .removeClass('border-success border-danger')
            .find('.card-body').removeClass('bg-success bg-danger bg-opacity-10 text-success text-danger')
            .find('i').removeClass('text-success text-danger');

        if ($(this).is(':checked')) {
            $('#card-trancado-on')
                .addClass('border-danger')
                .find('.card-body').addClass('bg-danger bg-opacity-10 text-danger')
                .find('i').addClass('text-danger');
        } else {
            $('#card-trancado-off')
                .addClass('border-success')
                .find('.card-body').addClass('bg-success bg-opacity-10 text-success')
                .find('i').addClass('text-success');
        }
    });

    // Busca de CEP via API
    $('#buscar-cep').click(function () {
        const cepInformado = $('#agencia-cep').val().replace('-', '')
        $.get('https://opencep.com/v1/' + cepInformado).done(function (cep) {
            if (cep.erro) {
                alert('CEP não encontrado');
            } else {
                $('#cep-endereco').text(cep.logradouro);
                $('#cep-bairro').text(cep.bairro);
                $('#cep-cidade').text(cep.localidade + '/' + cep.uf);
                $('#cep-result').removeClass('d-none');
            }
        });
    });

    // Aceite de termos para abrir agência
    $('#agencia-aceite-termos').change(function () {
        if ($(this).is(':checked')) {
            $('#agencia-abrir-continuar').prop('disabled', false);
        } else {
            $('#agencia-abrir-continuar').prop('disabled', true);
        }
    });

    // Criação de conta na agência
    $('#search-agencia').change(function () {
        $.get('/api.php/agencia/buscar/' + $(this).val(), function (Data) {
            $('#agencia-info-alert, #agencia-info-card').hide();
            $('#btn-criar-conta').prop('disabled', true);
            if (Data.status == 'success') {
                $('#agencia-id').val(Data.data.ag_id);
                $('#agencia-info-numero').text(Data.data.numero);
                $('#agencia-info-endereco').text(Data.data.cep);
                $('#agencia-info-tipo').html('<i class="bi bi-' + (Data.data.chave != 5 ? 'unlock' : 'lock') + ' me-1"></i> ' + (Data.data.chave != 5 ? 'Agência Pública' : 'Agência Privada'));
                $('#agencia-info-gerente').text(Data.data.gerente);
                $('#agencia-codigo').prop('disabled', (Data.data.chave != 5 ? true : false));
                $('#btn-criar-conta').prop('disabled', false);
                $('#agencia-info-card').show();
            }
            if (Data.status == 'error') {
                $('#agencia-info-alert').show();
                $('#agencia-id').val('');
            }
        }, 'json');
    });

    // Promove as ações de busca de conta e agencia de uma pessoa para verificar se a mesma existe quando se trata de uma transferência entre contas
    $('#contaTransferirAgencia, #contaTransferirConta').change(function () {
        let destinatario = $('#contaTransferirDestinatario');
        let button = $('#contaTrasferirButton');

        if ($('#contaTransferirAgencia').val() > 0 && $('#contaTransferirConta').val() > 0) {
            $.post('/api.php/conta/conta_transferir', { agencia: $('#contaTransferirAgencia').val(), conta: $('#contaTransferirConta').val() }, function (data) {
                const obj = typeof data === 'string' ? JSON.parse(data) : data;
                if (obj.status == 'success') {
                    destinatario.text(obj.destinatario);
                    button.prop('disabled', false);

                } else {
                    destinatario.text('');
                    button.prop('disabled', true);
                }
            })
        }
    });

    $('#contaTransferirValor, .MaskValor').mask('#.##0,00', {
        reverse: true,
        translation: {
            '#': { pattern: /\d/, recursive: true }
        }
    });
    $('#contaTransferirValor').on('input', function () {
        const valor = $(this).val().replace('.', '').replace(',', '.');
        const saldo = $(this).attr('data-eb-saldo');
        if (valor <= 0 || isNaN(valor) || valor == null || valor == undefined || valor > saldo) {
            $(this).val('0,00');
        }
    });

    /* Pix */
    $('.PixChaveCopiar').on('click', function () {
        // copia a chave
        const chave = $(this).data('pixchave');
        navigator.clipboard.writeText(chave);
        // Altera o icone para o usuário perceber que foi copiado e em seguida retorna ao icone original
        var icon = $(this).find('i');
        icon.toggleClass('bi-copy bi-check');
        setTimeout(function () {
            icon.toggleClass('bi-copy bi-check');
        }, 1500)
    });
    $('.PixChaveColar').on('click', async function () { // Cola a chave do clipboard
        const local = $(this).data('eb-target');
        try {
            const texto = await navigator.clipboard.readText();
            $(local).val(texto);
        } catch (err) {
            console.error('Erro ao copiar o texto: ', err);
        }
    })

    // Depositar && Receber
    $('input[name="sendCashValue"], input[name="sendCashValueRemove"]').on('keyup', function () {
        const maxValue = parseFloat($(this).attr('data-eb-maxValue'));
        const valor = parseFloat($(this).val().replace('.', '').replace(',', '.'));
        if(maxValue > 0 && valor > maxValue){
            $(this).val('').focus();
            $('#sendCashValueMax').pulsar();
        }
    });

    // Receber
    $('#pixGerarQrCodeButton').on('click', function () {
        const valor = $('#contaTransferirValor').val().replace('.', '').replace(',', '.');
        const chave = $('#contaTransferirValor').data('eb-chave');

        if (!valor || valor <= 0) {
            alert('Informe um valor válido');
            return;
        }

        $.post('/api.php/conta/pix/receber', { valor, chave }, function (resp) {

            const resultado = $('#pixGerarQrCodeResultado'); // Div com todos os elementos
            const qrcodePayload = $('#pixGerarQrCodePayload'); // textarea com o payload do qr code
            const qrcodeImage = $('#pixGerarQrCodeImage'); // div com a imagem do qr code
            const errorMessage = $('#pixGerarErrorMessage'); // div com a mensagem de erro

            resultado.addClass('d-none');
            errorMessage.addClass('d-none');

            if (resp.status == 'success') { // se tudo ocorreu bem, então vamos remover o display none e exibir

                resultado.removeClass('d-none');
                qrcodePayload.val(resp.pix).select();
                qrcodeImage.html('');
                new QRCode(document.getElementById("pixGerarQrCodeImage"), {
                    text: resp.pix,
                    width: 320,
                    height: 320,
                    correctLevel: QRCode.CorrectLevel.H
                });
                $(document).find('#pixGerarQrCodeImage').find('img').addClass('mx-auto');

            } else { // se houve algum erro, então exibimos uma mensagem de erro;
                errorMessage.removeClass('d-none');
                $('#contaTransferirValor').select();
            }

        }, 'json');
    });

    // Enviar
    $('#pixEnviarCopiaeCola').off('click').click(function () { // Altera o botão para mudar entre inserir chave e copia e cola
        $('#pixEnviarModalCopy, #pixEnviarModalMain').toggleClass('d-none');
        if ($('#pixEnviarModalCopy').is(':hidden')) {
            $(this).html('<i class="bi bi-clipboard me-1"></i> Copiar e Colar');
        } else {
            $(this).html('<i class="bi bi-key me-1"></i> Inserir Chave');
        }
    });
    $('#pixEnviarCopyInsert').on('input', function () { // Executa as ações de busca do destinatário ao digitar os dados do PIX
        // Campos referente ao copia e cola
        const chaveInsert = $('#pixEnviarCopyInsertChave');
        const destinatarioInsert = $('#pixEnviarCopyInsertDestinatario');
        const destinatarioNomeInsert = $('#pixEnviarDestinatarioNome');
        const valorInsert = $('#pixEnviarCopyInsertValor');
        const payload = $(this).val();
        // Campos referente ao inserir chave
        const pixEnviarChave = $('#pixEnviarChave');
        const pixEnviarValor = $('#pixEnviarValor');

        if (payload.includes('BR.GOV.BCB.PIX')) { // Verifica se é um payload valido
            $.post('/api.php/conta/pix/colar', { payload }, function (resp) {
                const obj = (typeof resp === 'string') ? JSON.parse(resp) : resp; // Verifica se e uma string ou um objeto
                if (obj.status == 'success') {
                    // Define os valores dos campos em caso de successo.
                    destinatarioInsert.text(obj.nome);
                    valorInsert.text(obj.valor.toString().replace('.', ','));
                    chaveInsert.text(obj.chave);
                    pixEnviarValor.val(obj.valor);
                    pixEnviarChave.val(obj.chave);
                    destinatarioNomeInsert.val(obj.nome);

                } else {
                    // Zera os valores dos campos e informa o erro passado pelo api.
                    alert('Dados do pix inválidos');
                    destinatarioInsert.text('');
                    valorInsert.text('');
                    chaveInsert.text('');
                    pixEnviarValor.val('');
                    pixEnviarChave.val('');
                }
            });
        } else {
            alert('Os dados do pix não são válidos.')
        }
    })
    $('#pixEnviarChave').on('input', function () {
        const chave = $(this).val();
        // console.log(chave);
        $.post('/api.php/conta/pix/buscarChave', { chave }, function (resp) {
            const obj = (typeof resp === 'string') ? JSON.parse(resp) : resp; // Verifica se e uma string ou um objeto
            // console.log(obj);
            if (obj.status == 'success') {
                $('#pixEnviarDestinatario').text(obj.nome);
                $('#pixEnviarDestinatarioNome').val(obj.nome);
            }
        }, 'json');
    })

    // Leitor de QR Code
    if (typeof qrCodeReader === 'undefined') {
        var qrCodeReader = null; // Variável para armazenar o leitor de QR Code
    }

    

    $('#pixQRCodeModal').on('shown.bs.modal', function () { // Quando o modal for mostrado

        if (!qrCodeReader) {
            qrCodeReader = new Html5Qrcode("qr-reader"); // Inicializa o leitor de QR Code
        }

        qrCodeReader.start(
            { facingMode: "environment" },
            {
                fps: 5,
                qrbox: 250,
                disableFlip: false
            },
            function (qrCodeMessage) {

                if (!qrCodeMessage) {
                    alert('QR Code inválido');
                    return;
                }


                $('#pixEnviarModal').modal('show');
                $('#pixEnviarCopyInsert')
                    .val(qrCodeMessage)
                    .trigger('input');

                setTimeout(function () { $('#pixEnviarCopiaeCola').trigger('click'); }, 100);
                qrCodeReader.stop().then(() => {
                    $('#pixQRCodeModal').modal('hide');
                });

            },
            function (errorMessage) {
                console.warn("Erro leitura:", errorMessage);
            }

        );
    });
    $('#pixQRCodeModal').on('hidden.bs.modal', function () { // Quando o modal for fechado fecha a camera
        if (qrCodeReader) {
            qrCodeReader.stop().catch(() => { });
        }
    });

    $('#pixEnviarValorFormaAlt').change(function () { // Altera a forma de escolha do metodo de pagamento do pix
        $('#pixEnviarValorForma').val($(this).val());
    })


    // Notificacoes de Contas
    $('.NotificacaoItem, #NotificacaoTodos').click(function () {
        const modal = $('#notificacoesContaModal');
        const button = $('#notificacoesContaButton');
        const id = $(this).data('eb-notification');
        const item = $(this);
        const isNum = (!isNaN(parseFloat(id)) && isFinite(id) && typeof id !== 'boolean');
        const total = parseInt(button.find('span.badge').text().trim())
        const conta = modal.data('eb-conta');


        $.post('/api.php/conta/notificacoes/lido', { id, conta }, function (resp) {
            console.log(resp);

            const obj = (typeof resp === 'string') ? JSON.parse(resp) : resp; // Verifica se e uma string ou um objeto
            if (obj.status == 'success') {

                if (isNum) {
                    item.find('span').toggleClass('bi-envelope-fill bi-envelope-open');
                    button.find('span.badge').text(total - 1);

                } else {
                    button.find('span.badge').text(0);
                    button.find('span.badge').addClass('d-none');
                    modal.find('.NotificacaoItem').find('span.bi-envelope-fill').removeClass('bi-envelope-fill').addClass('bi-envelope-open');
                    modal.modal('hide');
                }

            }
        }, 'json');
    });

    // Cartões
    $('.contaCardBox').click(function () { // Redireciona para a tela de detalhes do cartão
        window.location = '/conta/' + $("#ContaMeuID").data('eb-conta') + '/cartoes/' + $(this).data('eb-cartao') + '/faturas';
    })
    $('.cartoesFaturaPagar').click(function () { // Abre o modal de pagar fatura
        const id = $(this).data('eb-id');
        if (!isNumeric(id)) {
            alert('Cartão Inválido!')

        } else {
            const vencimento = $(this).data('eb-vencimento');
            const valor = $(this).data('eb-fatura');
            const color = $(this).data('eb-color');
            const modal = $('#cartoesFaturaPagar');
            const saldo = $('#ContaMeuSaldo').data('eb-contasaldo');
            $('#cartoesFaturaPagarID').val(id);
            $('#ModalCartoesPagarTipo')
                .removeClass('text-bg-danger text-bg-success text-bg-dark text-bg-primary text-bg-info text-bg-secondary').addClass('text-bg-' + color)
                .text($(this).data('eb-tipo'));
            $('#ModalCartoesPagarFinal').text($(this).data('eb-final'));
            $('#ModalCartoesPagarFatura').text('R$ ' + valor.toString().replace('.', ','));
            $('#ModalCartoesPagarVencimento').text(vencimento);
            $('#ModalCartoesPagarColor').css('background-color', color);
            $('#ModalCartoesPagarID').val(id);
            modal.modal('show');
        }
    });


    /* Investimentos */
    $('.ebInvModal').click(function () {
        const modal = $('#invModalCofrinho');
        const color = $(this).data('eb-color');
        const tempo = $(this).data('eb-tempo');
        const tipo = $(this).data('eb-tipo');
        modal.find('.modal-header').attr('class', 'modal-header').addClass('text-bg-' + color);
        modal.find('.modal-title').html($(this).html());
        modal.find('#invModalTempoDiv').addClass('collapse').removeClass((tempo == 1 ? '' : 'collapse'));
        modal.find('#invModalTempoDiv input').prop('disabled', (tempo == 1 ? true : false)).attr('min', tempo);
        modal.find('#invModelTempoInfo').text('Mínimo de ' + tempo + (tempo == 1 ? ' mes' : ' meses'));
        modal.find('#invModalTipo').val(tipo);
    });

    /* Pagamento de contas */
    $('input.pagarContaCheckbox').change(function () {
        const saldo = parseFloat($('#pagarContaForm').data('eb-saldo'));
        const pagarForma = $('#pagarContaForma');
        let soma = 0;
        $('input.pagarContaCheckbox').each(function () {
            if ($(this).is(':checked')) {
                soma += parseFloat($(this).val());
            }
        });
        $('#pagarContaSoma').text(soma.toFixed(2).replace('.', ','));
        $('#pagarContaSomaColor').removeClass('text-primary text-danger').addClass(soma > saldo ? 'text-danger' : 'text-primary');
        pagarForma.find('option').each(function () {
            let saldoOption = parseFloat($(this).data('eb-saldo'));
            $(this).prop("disabled", (saldoOption < soma ? true : false));
        });
        pagarForma.val(
            pagarForma.find('option[value="' + pagarForma.val() + '"]:disabled').length ? null : pagarForma.val()
        )
    });
    $('#pagarContaForma').change(function () {
        const val = $(this).val();
        const saldoInfo = $('span.pagarContaInfoSaldo');
        const cartaoInfo = $('span.pagarContaInfoCartao');

        $('input.pagarContaCheckbox').trigger('change');

        if (val == 0) {
            saldoInfo.removeClass('d-none');
            cartaoInfo.addClass('d-none');
            return;
        }

        if (val > 1) {
            saldoInfo.addClass('d-none');
            cartaoInfo.removeClass('d-none');
            return;
        }

        saldoInfo.addClass('d-none');
        cartaoInfo.addClass('d-none');
    })

    /* Botão de copia do modal de configuração */
    $('input.ModalConfigCopyItem').change(function () {
        $('#ModalConfigCopySubmit').prop('disabled', ($('input.ModalConfigCopyItem:checked').length) ? false : true);
    });

    /* Componente de criação de itens do shopping  */ 
    $('button#shopItemSubmit').click(function(){
        
        const shopItemID = UniqID();
        var shopItemBox = $('#shopItemModel').html().replace('{id}', shopItemID);
        var quantidade = parseInt($('#shopItemQuantidade').val()); quantidade = (quantidade < 0 || isNaN(quantidade)) ? Math.round(Math.random(50)*1000) : quantidade;
        // Item que se tornará json
        var shopItem = {
            "title": $('#shopItemTitle').val(),
            "description": $('#shopItemDescription').val(),
            "price": parseFloat($('#shopItemValue').val()),
            "discountPercentage": $('#shopItemDesconto').val(),
            "stock": quantidade,
            "thumbnail": $('#shopItemImageMini').val(),
            "images": [
                $('#shopItemImage0').val(),
                $('#shopItemImage1').val(),
                $('#shopItemImage2').val()
            ]
        }

        // Verifica se o titulo e a descrição não estão em branco
        if (shopItem.title == '' || shopItem.description == '') {
            alert('O titulo e descrição do item precisa ser informado.');
            return;
        }

        // Verificar se o valor é acima de zero.
        if (shopItem.price <= 0) {
            alert('O valor do item precisa ser maior que zero.');
            return;
        }

        // Verificar se o desconto é acima de zero.
        if (shopItem.discountPercentage < 0) {
            alert('O desconto do item precisa ser maior que zero.');
            return;
        }

        // Verifica se a imagem 0 foi informada.
        if (shopItem.images[0] == '') {
            alert('A primeira imagem precisa ser informada.');
            return;
        }

        // Verifica se a miniatura foi informada. Se não for, atribui a imagem zero como miniatura
        if (shopItem.thumbnail == '') {
            shopItem.thumbnail = shopItem.images[0]
        }

        shopItemBox = shopItemBox
            .replace("{title}", shopItem.title)
            .replace("{price}", shopItem.price.toFixed(2))
            .replace("{discountPercentage}", shopItem.discountPercentage)
            .replace("{thumbnail}", shopItem.thumbnail)
            .replace("{stock}", shopItem.stock)
            .replace("{priceReal}", (shopItem.price * parseFloat($('#shopNewItem').data('eb-dolar'))).toFixed(2))
        ;

        // Adiciona o item ao shop
        $('#shopItems').append(shopItemBox);
        $(document).find('input[name="shop['+shopItemID+']"]', '#shopItems').val(JSON.stringify(shopItem));

        // Controle de edição do item
        let EditItemID = $(this).attr('data-eb-target');
        console.log(EditItemID);
        if(EditItemID != undefined && EditItemID != ''){
            $('div.shopItemBox[data-eb-id="'+EditItemID+'"]').remove();
        } $(this).attr('data-eb-target','');
        

        $('#shopNewItem').modal('hide');
    });

    // Atualiza o valor em real baseado na cotação do dolar e no valor inserido no input

    $('#shopItemValue').keyup(function(){
        const valor = (parseFloat($(this).val()) * parseFloat($('#shopNewItem').data('eb-dolar'))).toFixed(2);
        $('#shopItemRealValue').text( valor == 'NaN' ? '0.00' : valor.toString().replace('.', ','));
    })

    $(document).on('click', '.ishopItemTrash', function() {
        $(this).closest('.shopItemBox').remove();
    });

    // Edita o item do shop
    $('button.ishopItemEdit').click(function(){
        let Json = JSON.parse($(this).closest('.shopItemBox').find('.shopItemBoxJson').val());
        const id = UniqID();
        // Atribui o id unico ao campo editado
        $(this).closest('.shopItemBox').attr('data-eb-id', id);
        // Atribui os valores ao campo do modal
        $('input#shopItemTitle').val(Json.title);
        $('textarea#shopItemDescription').val(Json.description);
        $('input#shopItemValue').val(Json.price);
        $('input#shopItemQuantidade').val(Json.stock);
        $('input#shopItemDesconto').val(Json.discountPercentage);
        $('input#shopItemImageMini').val(Json.thumbnail);
        $('input#shopItemImage0').val(Json.images[0]);
        $('input#shopItemImage1').val(Json.images[1]);
        $('input#shopItemImage2').val(Json.images[2]);
        // Atribui o ID do item editado e exibe o moda
        $('button#shopItemSubmit').attr('data-eb-target', id);
        $('#shopNewItem').modal('show');
    });

    // Remove o item do shop que estava sendo editado
    // $('button#shopItemSubmit').click(function(){
    //     const id = $(this).data('eb-target');
    //     if(id != undefined && id != ''){
    //         $('div.shopItemBox[data-eb-id="'+id+'"]').remove();
    //     }
    //     $(this).attr('data-eb-target','');
    // });

    // Compra do item do shop
    $('#compraModalMetodo').change(function () {
        const compraMetodo = $('#compraModalMetodo');
        var parcelas = $('#compraModalParcela');
        parcelasQT = parseInt((isNaN(parcelas.val())) ? 1 : parcelas.val()); // Quantidade de parcelas
        const valor = parseFloat($('#compraModalValorPagar').val());
        const valorDiscount = parseFloat($('#compraModalValorPagarDiscount').val());
        const quantidade = parseInt($('#compraModalQuantidade').val());
        const metodo = parseInt($(this).val());
        const valorTexto = $('#compraModalValor');
        var valorTotal = 0;

        parcelas.prop('disabled', (metodo == 0 ? true : false)); // Desabilita o select de parcelas se o metodo for dinheiro

        // Calcula o valor com desconto para pagamento a vista
        if (metodo == 0 || (metodo != 0 && parcelasQT == 1)) {
            valorTotal = valorDiscount * quantidade;

        } else {
            // Calula o valor sem desconto e sem juros
            if (parcelasQT <= 3) {
                valorTotal = valor * quantidade;

            } else {
                // Calcula o valor com desconto e com juros de 5%
                valorTotal = valor * (1 + 0.05 * parcelasQT) * quantidade;
            }
        }

        // Verifica se o cartão tem limite e se não tiver, desativa ele
        compraMetodo.find('option').each(function () {
            if ($(this).val() > 0) {
                $(this).prop('disabled', (($(this).data('eb-limite') < valorTotal) ? true : false));
            }
        });
        compraMetodo.val(
            compraMetodo.find('option[value="' + compraMetodo.val() + '"]:disabled').length ? null : compraMetodo.val()
        );

        // Formata o valor e imprime na tela
        valorTotal = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(valorTotal)
        valorTexto.text(valorTotal);
    });

    $('#compraModalQuantidadeMenos, #compraModalQuantidadeMais').click(function () {
        var quantidade = $('#compraModalQuantidade');
        const maximo = parseInt(quantidade.attr('max'));
        const numero = parseInt($(this).data('eb-quantidade'));
        var valor = parseInt($('#compraModalQuantidade').val()) + numero;
        valor = (valor < 1) ? 1 : (valor > maximo) ? maximo : valor;
        quantidade.val(valor);
        $('#compraModalMetodo').trigger('change');
    });

    $('#compraModalQuantidade').change(function () {
        var quantidade = $('#compraModalQuantidade');
        const maximo = parseInt(quantidade.attr('max'));
        var valor = parseInt($(this).val());
        valor = (valor < 1) ? 1 : (valor > maximo) ? maximo : valor;
        quantidade.val(valor);
        $('#compraModalMetodo').trigger('change');
    });

    $('#compraModalParcela').change(function () {
        $('#compraModalMetodo').trigger('change');
    });

    // Shopping Itens Gerencia
    $('.iShopItemReduce,.iShopItemIncrease').click(function() {
        const inValor = $(this).hasClass('iShopItemReduce') ? -1 : 1;
        const max = parseInt($(this).closest('li.iShopItem').removeClass('opacity-25').find('span.iShopItemContaStock').text());
        let input = $(this).closest('div.input-group').find('input.iShopItemStock');
        let valor = parseInt(input.val()) + parseInt(inValor);
        input.val((valor < 0) ? 0 : valor);
    });

    $('input.iShopItemStock').change(function() {
        const max = parseInt($(this).closest('li.iShopItem').removeClass('opacity-25').find('span.iShopItemContaStock').text());
        let valor = parseInt($(this).val());
        $(this).val((valor < 0) ? 0 : valor);
    });

    $('button.iShopItemTrash').click(function(){
        $(this).closest('li.iShopItem').addClass('opacity-25').find('input.iShopItemStock').val(0);
    });



    // SimpleTables
    const SimpleTablesDefault = {
        searchable: true,
        sortable: true,
        paging: true,
        perPage: 50,
        labels: {
            placeholder: "Buscar...",
            perPage: "Itens por página",
            noRows: "Nenhum registro encontrado",
            info: "Mostrando {start} a {end} de {rows} registros"
        }
    };

    function EBDatatables(seletor, opcoesPersonalizadas = {}) {
        if (typeof simpleDatatables === 'undefined') {
            console.error('simpleDatatables não foi carregado');
            return null;
        }
        
        // Mescla as opções padrão com as personalizadas (se houver)
        const opcoesFinais = { ...SimpleTablesDefault, ...opcoesPersonalizadas };
        
        return new simpleDatatables.DataTable(seletor, opcoesFinais);
    }

    // Tabela de Usuários para exibição root
    if($('#AdminUserList').length){ const rootUserList = EBDatatables('#AdminUserList'); }
    // Tabela de Ranking - Shop
    var tableReorder = {};
    if($('table.tableReorder').length){ 

        $('table.tableReorder').each(function(chave, table){
            let eID = $(this).attr('id');
            tableReorder[eID] = EBDatatables('#'+eID, {sort: {column: 2, order: 'desc'}}); // Cria o objeto da tabela
            // Adiciona o botão de exportação
            $('#'+eID).closest('div').prepend('<div class="my-1">'+
                '<button type="button" class="btn btn-sm btn-secondary simpleDatatablesExport" data-eb-target="'+eID+'">Exportar CSV</button>'+
                
            '</div>');
        });

        console.log(tableReorder);

        $('table.tableReorder thead tr th').click(function(){
            var index = 1;
            $('table.tableReorder tbody tr').each(function(){
                $(this).find('td:first').text(index);
                index++;
            });
        }).trigger('click');
    
    }
    // Tabela de Pendencias das contas pela gerencia
    if($('#gerenciaPendenciasContas').length){
        const gerPendenciasContasID = 'gerenciaPendenciasContas';
        const gerPendenciasContas = EBDatatables('#' + gerPendenciasContasID);
        $('input[type="search"][aria-controls="'+gerPendenciasContasID+'"]').focus();

        $('button.getPendenciasContasButtonFinalizar').click(function(){
            $('#'+gerPendenciasContasID).find('input[type="checkbox"]').attr('checked',false);
            $(this).closest('tr.iPendenciasConta').find('input[type="checkbox"]').attr('checked',true);
            $('#gerenciaPendenciasSend').trigger('click');
        });

        $('button#gerenciaPendenciasCheckAll').click(function(){
            $('#'+gerPendenciasContasID).find('input[type="checkbox"]').attr('checked',true);
        });
    }

    $(document).on('click', 'button.simpleDatatablesExport', function(){
        const eID = $(this).data('eb-target');
        const tableInstance = tableReorder[eID];
        
        if (tableInstance && window.simpleDatatables && window.simpleDatatables.exportCSV) {
            // Usa a função de exportação separada (versão 5+)
            window.simpleDatatables.exportCSV(tableInstance, {
                download: true,
                filename: eID
            });
        } else if (tableInstance && typeof tableInstance.export === 'function') {
            // Fallback para versões antigas
            tableInstance.export({
                type: "csv",
                filename: eID,
                download: true
            });
        } else {
            console.error('Função de exportação não disponível');
            console.log('Objetos disponíveis:', {
                simpleDatatables: window.simpleDatatables,
                exportCSV: window.simpleDatatables?.exportCSV,
                tableInstance: tableInstance
            });
        }
    });


    // Configuração e Edição de Subgerentes das agências
    $('#ModalSubGerenteSearch').click(function(){
        var emails = $('#ModalSubGerenteEmail').val();

        $.post('/api.php/usuario/buscar/email', { emails }, function(data){

            if(!typeof data === 'object' && data !== null){
                data = JSON.parse(data);
            }

            if(data.status == 'error'){
                alert(data.msg);
                return;
            }

            if(data.status == 'success'){                    
                $.each(data.data, function(chave, valor){
                    $('#ModalSubGerenteList').prepend(
                    `<li class="list-group-item d-flex justify-content-between">
                        <div class="align-self-center">
                            <input type="hidden" name="subgerente[${valor.user_id}]" value="insert">
                            ${valor.user_nome.trim()}
                            <br/>
                            <small class="ft-10 opacity-50">${valor.user_email.trim()}</small>
                        </div>
                        <button class="btn btn-sm btn-danger iTrash align-self-center" type="button">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </li>`);

                });
                $('#ModalSubGerenteSearch').closest('div.row').find('div.d-none').removeClass('d-none');
                return;
            }
        });
    });

    // Remove o item da listagem de gerentes
    $(document).on('click', '#ModalSubGerente button.iTrash', function(){
        $(this).closest('li.list-group-item').remove();
    });

    // Configuração da selecao dos elementos dos cartoes para impressao
    $('#GerenciaCardListGroupSelectAll').click(function(){
        $('#GerenciaCardListGroup').find('input[type="checkbox"]').attr('checked',true);
    });
    $('button.GerenciaCardListGroupPrint').click(function(){
        $('#GerenciaCardListGroup').find('input[type="checkbox"]').attr('checked',false);
        $(this).closest('.list-group-item').find('input[type="checkbox"]').attr('checked',true);
        $('#GerenciaCardListGroupForm').submit();
    })
    $('button#GerenciaCardListGroupSubmiter').click(function(){
        $('#GerenciaCardListGroupForm').submit();
    })


    // Sistema PicToPay
    if($('#PicToPay').length){

        var PicToPaySend = {
            tapValor: 0,
            tapType: 1,
            tapBreak: false,
            agencia: null,
            tapCode: null,
            tapPay: 0,
            maxValue: 0,

            // Elementos e configuração de cores
            _pauseEl: null,
            _reader: null,
            _colors: {
                processing: 'text-bg-warning',
                success: 'text-bg-success',
                error: 'text-bg-danger',
                idle: 'text-bg-secondary'
            },
            fx: function(status, opts) {
                opts = opts || {};
                var self = this;

                var $pause = this._pauseEl || $('#PicToPayPause');
                if (!$pause.length) return;

                if (status === 'idle') {
                    $pause.addClass('d-none');
                    $pause.find('.PicToPayPauseStatus').addClass('d-none');

                    if (opts.resume && self._reader && self._reader.isScanning) {
                        self._reader.resume();
                    }
                    return;
                }

                // Remove todas as cores conhecidas e aplica a do status
                var color = this._colors[status] || this._colors.idle;
                $pause
                    .removeClass(Object.values(this._colors).join(' '))
                    .addClass(color)
                    .removeClass('d-none');

                // Esconde todos os status e mostra só o pedido
                $pause.find('.PicToPayPauseStatus').addClass('d-none');
                $pause.find('.PicToPayPauseStatus[data-eb-status="' + status + '"]').removeClass('d-none');

                // Texto extra (erro)
                if (typeof opts.text === 'string') {
                    $pause.find('#PicToPayPauseErrorText').text(opts.text);
                }

                // Auto-ocultar
                if (opts.hideIn && opts.hideIn > 0) {
                    setTimeout(function() {
                        self.fx('idle');
                        if (opts.resume && self._reader) {
                            self._reader.resume();
                        }
                    }, opts.hideIn);
                }
            },

            // Atalhos
            fxProcessing: function() {
                this.fx('processing');
            },
            fxSuccess: function(ms) {
                this.fx('success', {
                    hideIn: ms || 2000,
                    resume: true
                });
            },
            fxError: function(text, ms) {
                this.fx('error', {
                    text: text,
                    hideIn: ms || 3500,
                    resume: true
                });
            }
        }

        $('#PicToPayMainCodeShow').text($('#PicToPay').data('eb-tapcode'));

        // PicToPay -> Start
        $('#PicToPayMainStart').click(function() {
            PicToPaySend.maxValue = $('#PicToPayMainValor').attr('data-eb-maxValue');
            PicToPaySend.maxValue = PicToPaySend.maxValue > 0 ? PicToPaySend.maxValue : 0;
            PicToPaySend.tapValor = parseFloat($('#PicToPayMainValor').val().replaceAll('.', '').replace(',', '.'));
            PicToPaySend.tapType = parseInt($('input[name="pictopaymaintype"]:checked').val());
            PicToPaySend.tapBreak = ($('#PicToPayMainBreak').is(':checked') ? 1 : 0);
            PicToPaySend.agencia = $('#PicToPay').data('eb-agencia');
            PicToPaySend.tapCode = $('#PicToPay').data('eb-tapcode');

            console.log(PicToPaySend.maxValue);

            if (PicToPaySend.tapValor > 0 && (PicToPaySend.maxValue == 0 || (PicToPaySend.maxValue > 0 && PicToPaySend.tapValor <= PicToPaySend.maxValue))) {
                $('#PicToPayValorInfo').text('R$ ' + PicToPaySend.tapValor.toFixed(2).replace('.', ','));
                $('#PicToPayTypeEntrada, #PicToPayTypeSaida').addClass('d-none');
                if (PicToPaySend.tapType == 0) {
                    $('#PicToPayTypeEntrada').removeClass('d-none');
                } else {
                    $('#PicToPayTypeSaida').removeClass('d-none');
                }
                console.log(PicToPaySend);
                $('#PicToPay').modal('show');
            
            } else {
                $('#PicToPayMainValorMaxCash').pulsar();
                $('#PicToPayMainValor').focus().select();
            }
        });

        // PicToPay Inicia o modal para leitura de QR Code
        if (typeof qrCodeReader === 'undefined') {
            var qrCodeReader = false;
        }
        PicToPaySend.tapPay = 0;

        $('#PicToPay').on('shown.bs.modal', function() { // Quando o modal for mostrado

            let x = window.ebScreen.x;
            let y = window.ebScreen.y;
            let d = parseInt($('#PicToPayCamera').offset().top.toFixed(0))
            let newY = y * (((y - d) / y) * 0.95);
            $('#PicToPayCamera').css('cssText', 'height: ' + newY + 'px !important;');

            const statusBar = $(this).find('#PicToPayPause');
            const statusBarColor = 'text-bg-secondary text-bg-success text-bg-warning text-bg-danger';

            if (!qrCodeReader) {
                qrCodeReader = new Html5Qrcode("qr-reader"); // Inicializa o leitor de QR Code
                PicToPaySend._reader = qrCodeReader; // guarda referência
            }

            qrCodeReader.start({
                    facingMode: "user" // "environment" or "user"
                }, {
                    fps: 2,
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                        let qrboxSize = Math.floor(minEdge * 0.7);
                        return {
                            width: qrboxSize * 1.2,
                            height: qrboxSize
                        };
                    },
                    aspectRatio: 1.0,
                    disableFlip: false,
                },
                function(qrCodeMessage) {

                    statusBar.addClass('d-none');

                    if (!qrCodeMessage) {
                        console.log('QR Code inválido');
                        statusBar.filter('[data-eb-status="erro"]').removeClass('d-none');
                        return;
                    }

                    qrCodeReader.pause(true);
                    PicToPaySend.fxProcessing();

                    $.ajax({
                        url: '/api.php/agencia/' + PicToPaySend.agencia + '/picToPay',
                        type: 'POST',
                        data: {
                            agencia: PicToPaySend.agencia,
                            tapValue: PicToPaySend.tapValor,
                            tapType: PicToPaySend.tapType,
                            tapBreak: PicToPaySend.tapBreak,
                            tapCode: PicToPaySend.tapCode,
                            qrCode: qrCodeMessage
                        },
                        dataType: 'json',
                        success: function(response) {
                            // console.log(response);

                            if (response.status === 'success') {
                                PicToPaySend.tapPay++;
                                $('.PicToPayTapQt').text(PicToPaySend.tapPay);

                                PicToPaySend.fxSuccess(2000);
                                return;
                            }

                            if (response.status === 'error') {
                                PicToPaySend.fxError(response.message || 'Falha na transação', 3000);
                                return;
                            }

                            // status inesperado
                            PicToPaySend.fxError('Resposta inesperada do servidor', 3000);
                        },
                        error: function(xhr, status, err) {
                            // console.error(xhr, status, err);
                            PicToPaySend.fxError('Erro de comunicação', 3000);
                        }
                    });

                },
                function(errorMessage) {
                    // alert('Erro na leitura do qrCode!');
                    // console.warn("Erro leitura:", errorMessage);
                }
            );

        });
        $('#PicToPay').on('hidden.bs.modal', function() { // Quando o modal for fechado fecha a camera
            if (qrCodeReader) {
                qrCodeReader.stop().catch(() => {});
            }
        });

    };

});

function goTop() { $('html, body').animate({ scrollTop: 0 }, 'fast'); } // Scroll to top of page
function UniqID() { return '_' + Math.random().toString(36).substr(2, 9); } // Gera um ID único simples