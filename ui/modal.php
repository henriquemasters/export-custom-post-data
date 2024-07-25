<style>
    .preloader {
        display: none; /* oculta o modal por padrão */
        position: fixed; /* posição fixa para que o modal não role com a página */
        z-index: 99999; /* define a ordem de empilhamento do modal */
        left: 0;
        top: 0;
        width: 100%; /* largura total do modal */
        height: 100%; /* altura total do modal */
        overflow: auto; /* habilita o scroll caso o conteúdo do modal seja maior do que a tela */
        background-color: rgba(0, 0, 0, 0.4); /* cor de fundo semi-transparente */

        align-items: center;
        justify-content: center;
    }

    .preloader h2 {
        color: #fff;
        font-weight: bold;
        font-size: 1.5rem;
        text-align: center;
        text-shadow: 2px 2px 2px #000000;
    }

    .modal {
        display: none; /* oculta o modal por padrão */
        position: fixed; /* posição fixa para que o modal não role com a página */
        z-index: 9999; /* define a ordem de empilhamento do modal */
        left: 0;
        top: 0;
        width: 100%; /* largura total do modal */
        height: 100%; /* altura total do modal */
        overflow: auto; /* habilita o scroll caso o conteúdo do modal seja maior do que a tela */
        background-color: rgba(0, 0, 0, 0.4); /* cor de fundo semi-transparente */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* centraliza o modal verticalmente */
        padding: 20px;
        border: 1px solid #888;
        width: 30%; /* largura do conteúdo do modal */

        -webkit-border-radius: 8px;
        -moz-border-radius: 8px;
        border-radius: 8px;
    }

    .modal-header h2 {
        margin-top: 0;
    }
    
    .modal-body p {
        font-size: 1rem;
    }

    .modal-footer {
        margin-top: 1.2rem;
        padding: 10px 0;
        text-align: right;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .btn {
        padding: 1rem;
        text-decoration: none;

        -webkit-border-radius: 8px;
        -moz-border-radius: 8px;
        border-radius: 8px;
    }
    .btn-primary {
        background-color: #2271b1;
        color: #fff !important;
    }
</style>

<div class="preloader">
    <h2>
        Carregando arquivo... Aguarde.<br><br>
        <img src="https://i.gifer.com/origin/34/34338d26023e5515f6cc8969aa027bca_w200.gif" height="48" alt=""/>
    </h2>
</div>

<div id="myModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>
            <h2 style="text-transform: uppercase">Atenção: Exportando dados em CSV</h2>
        </div>
        <hr>
        <div class="modal-body">
            <p style="text-align: justify; color: #000">
                <strong>Este processo pode levar algum tempo para ser concluído, especialmente se houver um grande volume de registros.</strong>
            </p>

            <p style="text-align: justify">
                Para garantir que o processo seja concluído com sucesso, pedimos gentilmente que você <strong style="color: #000">não feche esta aba do navegador</strong> 
                até que seja solicitado a confirmar o nome do arquivo e escolher o local para salvar o arquivo exportado.
            </p>

            <p style="text-align: justify">Agradecemos a sua paciência e compreensão enquanto trabalhamos para fornecer os dados solicitados. Se você tiver alguma 
                dúvida ou preocupação, não hesite em entrar em contato conosco.
            </p>

            <p>Obrigado.</p>
            <p style="text-align: center; color: #000"><strong>Deseja prosseguir com a exportação de todos registros agora?</strong></p>
        </div>
        <hr>
        <div class="modal-footer">
            <a href="javascript:;" class="btn btn-default close-btn">Cancelar</a>
            <a href="javascript:;" id="export-csv" class="btn btn-primary">Prosseguir com a exportação</a>
        </div>
    </div>
</div>

<script>
    var btn = document.getElementById("doaction");
    var select = document.getElementById("bulk-action-selector-top");
    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];
    var closeBtn = document.getElementsByClassName("close-btn")[0];
    var exportCsvButton = document.querySelector('#export-csv');
    var preloader = document.getElementsByClassName("preloader")[0];

    // adiciona o listener de alteração no campo select
    btn.onclick = function () {
        event.preventDefault();
        // verifica se o valor selecionado é "valor1"
        if (select.value === "gerar_arquivo") {
            // se for, executa a função de abertura do modal
            modal.style.display = "block";
        }
    };

    // quando o usuário clicar no <span> (x) ou no botão close-btn, fecha o modal
    span.onclick = function () {
        modal.style.display = "none";
        location.reload();
    };

    closeBtn.onclick = function () {
        modal.style.display = "none";
        location.reload();
    };

    // Aguarda o carregamento completo da página antes de executar o código
    document.addEventListener('DOMContentLoaded', function () {

        // Adiciona um ouvinte de evento de clique ao botão de exportação de CSV
        exportCsvButton.addEventListener('click', function () {
            preloader.style.display = "flex";

            // Faz uma solicitação POST para o arquivo "admin-ajax.php" no servidor
            fetch('<?= admin_url('admin-ajax.php') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: "action=gerar_arquivo&post_type=<?= $_GET['post_type'] ?>" // Define o corpo da solicitação com o parâmetro de ação "gerar_arquivo"
            }).then(response => response.blob()) // Extrai o blob de resposta
                    .then(blob => {
                        var filename = '<?= ($_GET['post_type'] . "_" . date('YmdHis') . ".csv") ?>';

                        // Cria um elemento de link HTML
                        var link = document.createElement('a');

                        // Define o link de download do arquivo como o URL do blob
                        link.href = URL.createObjectURL(blob);

                        // Define o nome do arquivo de download
                        link.download = filename;

                        // Esconde o link de download
                        link.style.visibility = 'hidden';

                        // Adiciona o link ao corpo do documento
                        document.body.appendChild(link);

                        // Clica no link para iniciar o download do arquivo
                        link.click();

                        // Remove o link do corpo do documento
                        document.body.removeChild(link);

                        location.reload();
                    })
                    .catch(error => console.error(error)); // Registra qualquer erro no console
        });
    });

</script>