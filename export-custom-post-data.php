<?php

//  ini_set('display_errors', '1');
//  ini_set('display_startup_errors', '1');
//  error_reporting(E_ALL);

ini_set('memory_limit', '-1');

/**
 * Plugin Name: Export Custom Post Data
 * Description: Exporta dados de um Custom Post Type para arquivo CSV.
 * Version: 2.0.0
 * Author: Henrique Mariano S. Silva
 * Author URI: mailto:henrique.mariano@montreal.com.br
 */
require __DIR__ . '../../../vendor/autoload.php';

use League\Csv\Writer;
use Nette\Utils\Strings;

// Verificação padrão do carregamento do diretório de instalação do plugin...
if (!defined('ABSPATH')) {
    die('Invalid request');
}

class ExportCustomPostData {

    private $post_types = array(
        'mail-home',
        'mail-corr-bancario',
        'mail-parc-simples',
        'mail-integr-e-distr',
        'mail-coop-de-cred',
        'mail-inovacao-mpe',
        'mail-inovacao-mge'
    );

    /**
     * Constructor default
     *
     * @return void
     */
    public function __construct() {
        # 1. Modifica a variável global WP_MEMORY_LIMIT
        add_action('init', array($this, 'updateWpMemoryLimit'));

        # 2. Adiciona ao "Bulk Actions" a opção de exportação.
        foreach ($this->post_types as $post_type) {
            add_filter("bulk_actions-edit-{$post_type}", array($this, 'add_export_bulk_option'));
        }

        # 4. Adiciona um hook para a função "gerar_arquivo" para permitir a chamada via AJAX
        add_action('wp_ajax_gerar_arquivo', array($this, 'gerar_arquivo'));

        // Adiciona no menu do painel o acesso às configirações do Plugin...
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Hooks padrões de ativação, desativação e desintalação do Plugin...
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));
    }

    /**
     * Adiciona ao "Bulk Actions" a opção de exportação.
     * E ao carregar a página, acrescenta o script HTML, CSS e JS que renderiza o modal
     *
     * @param array $actions
     * @return array
     */
    public function add_export_bulk_option(array $actions): array {

        $actions['gerar_arquivo'] = __('Exportar para Arquivo CSV', 'textdomain');

        # 3. Adiciona ao carregar a página o script HTML, CSS e JS que renderiza o modal
        add_action('admin_footer-edit.php', array($this, 'adicionar_modal_html'));

        return $actions;
    }

    /**
     * O HTML, CSS e Javascript de renderização do modal
     * 
     * @return void
     */
    public function adicionar_modal_html(): void {
        include __DIR__ . '/ui/modal.php';
    }

    /**
     * Cria e disponibiliza para o usuário o arquivo CSV exportado.
     *
     * @return void
     */
    public function gerar_arquivo(): void {

        $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';

        if (in_array($post_type, $this->post_types)) {
            $data = $this->getFromDatabase($post_type);

            try {
                $csv = Writer::createFromFileObject(new SplTempFileObject());
                $csv->insertOne(array_keys($data[0])); // Cabeçalhos
                $csv->insertAll($data);
                echo $csv->getContent();
            } catch (Exception $exc) {
                echo $exc->getMessage() . PHP_EOL;
                echo nl2br($exc->getTraceAsString());
            }
        } else {
            echo "'Custom post_type' inválido.";
        }
        exit;
    }

    /**
     * @return void
     */
    public function updateWpMemoryLimit(): void {
        define('WP_MEMORY_LIMIT', FALSE);
    }

    /**
     * Busca as informações do banco de dados e monta um array para exportação.
     *
     * @param string $post_type O tipo de post a ser exportado.
     * @return array
     * @global type $wpdb
     */
    private function getFromDatabase(string $post_type): array {
        global $wpdb;
        $data = [];

        $fields = $this->getCustomFields($post_type);

        $this->sanitizeDatabase($wpdb, ['tenable.com', 'burpcollaborator'], $post_type);

        $query = new WP_Query([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'orderby' => 'id',
            'order' => 'ASC',
            'posts_per_page' => -1,
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_data = array();
                foreach ($fields as $field) {
                    $post_data[$field['label']] = get_field($field['name'], get_the_ID());
                }
                $data[] = $post_data;
            }
        }
        wp_reset_postdata();

        return $data;
    }

    /**
     * Obtém os campos personalizados para um tipo de post.
     *
     * @param string $post_type O tipo de post.
     * @return array
     */
    private function getCustomFields(string $post_type): array {
        $fields = array();

        $field_groups = acf_get_field_groups(['post_type' => $post_type]);

        foreach ($field_groups as $field_group) {
            foreach (acf_get_fields($field_group) as $acf_field) {
                $fields[] = [
                    'name' => $acf_field['name'],
                    'label' => $acf_field['label'],
                ];
            }
        }

        return $fields;
    }

    /**
     * Sanitiza o banco de dados excluindo posts e metadados de postagem associados a valores na lista negra.
     *
     * @param wpdb $database O objeto de banco de dados do WordPress.
     * @param array $blacklisted_values Um array de valores na lista negra para pesquisar nos valores de metadados de postagem.
     *
     * @throws Exception se a consulta falhar.
     *
     * @return bool True se o banco de dados for sanitizado com sucesso, false caso contrário.
     */
    private function sanitizeDatabase(wpdb $database, array $blacklisted_values, string $post_type): bool {
        try {
            // Escape os valores da lista negra.
            $escaped_values = array_map(function ($value) use ($database) {
                return '%' . $database->esc_like($value) . '%';
            }, $blacklisted_values);

            // Construa a parte da consulta SQL para as cláusulas LIKE para a lista negra.
            $not_like_conditions = implode(' OR ', array_fill(0, count($escaped_values), 'bpmeta.meta_value LIKE %s'));

            // Adicione a cláusula WHERE para verificar o tipo de postagem.
            $where_clause = "bp.post_type = %s";

            // Construa a consulta com a lista negra de valores.
            $query = $database->prepare("SELECT DISTINCT post_id 
            FROM {$database->postmeta} AS bpmeta
            JOIN {$database->posts} AS bp ON bpmeta.post_id = bp.ID
            WHERE $where_clause
            AND (
                $not_like_conditions
                OR (
                    bpmeta.meta_value = '' 
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM {$database->postmeta} AS bpm 
                        WHERE bpm.post_id = bpmeta.post_id 
                        AND bpm.meta_value != ''
                    )
                )
            )
            AND SUBSTRING(bpmeta.meta_key, 1, 1) != '_' ORDER BY bpmeta.post_id DESC",
                    $post_type,
                    ...$escaped_values
            );

            // Execute a consulta.
            // Para debuggar, não excluir esta linha: $results = $database->get_results($query, ARRAY_A);
            $post_ids = $database->get_col($query);

            if (!empty($post_ids)) {
                // Construa uma string de parâmetros para a consulta usando a função implode().
                $params = implode(',', $post_ids);

                //  Para debuggar, não excluir esta linha: echo '<pre>' . var_export($params, true) . '</pre>'; exit();

                // Exclua todos os posts e seus campos personalizados associados em uma única consulta.
                $database->query(
                        "DELETE p, pm FROM {$database->posts} p " .
                        "INNER JOIN {$database->postmeta} pm ON p.ID = pm.post_id " .
                        "WHERE p.ID IN ($params)"
                );
            }

            return true;
        } catch (Exception $e) {
            // Registre o erro e lance uma exceção.
            error_log('Erro ao sanitizar o banco de dados: ' . $e->getMessage());
            throw new Exception('Falha ao sanitizar o banco de dados.');
        }
    }

    /**
     * Cria o acesso à página de configurações do plugin.
     *
     * @return void
     */
    public function add_settings_page(): void {
        add_options_page(
                'Export Custom Post Data - Configurações',
                'Export Custom Post Data',
                'manage_options',
                'export-custom-post-data',
                array($this, 'render_settings_page')
        );
    }

    /**
     * Responsável por renderizar a página de configuração do plugin. Localizado em
     * Settings (Configurações) no menu principal do Painel Administrativo do WordPress.
     *
     * @return void
     */
    public function render_settings_page(): void {
        echo "<div class='wrap'> <h1>" . esc_html(get_admin_page_title()) . "</h1> <p>No momento, este plugin não tem nenhuma configuração.</p></div>";
    }

    public function activate() {
        // Adicione opções ou outro código de inicialização aqui...
    }

    public function deactivate() {
        // Limpe resquícios de sua instalação aqui, se necessário...
    }

    public static function uninstall() {
        // Limpe resquícios de sua instalação aqui, se necessário...
    }
}

// Executando...
if (class_exists('ExportCustomPostData')) {
    new ExportCustomPostData();
}

