# Export Custom Post Data

**Versão:** 2.0.0  
**Autor:** Henrique Mariano S. Silva  
**Autor URI:** [henrique.masters@gmail.com](mailto:henrique.masters@gmail.com)

## Descrição

O plugin "Export Custom Post Data" foi desenvolvido para exportar dados de tipos de posts personalizados (Custom Post Types) para um arquivo CSV no WordPress. Ele é especialmente útil para projetos que precisam extrair informações específicas para análise ou arquivamento.

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- Extensão PHP `mbstring`

## Instalação

1. **Baixar o Plugin:**
   - Clone ou baixe este repositório como um arquivo `.zip`.

2. **Instalar o Plugin:**
   - No painel administrativo do WordPress, vá para `Plugins > Adicionar Novo > Enviar Plugin`.
   - Selecione o arquivo `.zip` baixado e clique em "Instalar Agora".

3. **Ativar o Plugin:**
   - Após a instalação, ative o plugin em `Plugins > Plugins Instalados`.

## Uso

1. **Exportação de Dados:**
   - Vá para a lista de posts do tipo personalizado que deseja exportar.
   - No menu de ações em massa (Bulk Actions), selecione "Exportar para Arquivo CSV".
   - Siga as instruções para completar a exportação.

2. **Configurações:**
   - No momento, este plugin não possui configurações adicionais. Todas as funcionalidades estão disponíveis diretamente através das ações em massa nos tipos de post personalizados.

## Desenvolvimento e Testes

### Ambiente de Desenvolvimento

1. **Pré-requisitos:**
   - Composer para gerenciamento de dependências PHP.
   - Node.js e npm para ferramentas de build (opcional).

2. **Configuração do Ambiente:**
   - Clone o repositório para sua máquina local.
   - Execute `composer install` para instalar as dependências do PHP.

### Executando Testes

Atualmente, este plugin não possui um conjunto de testes automatizados. Recomenda-se verificar a funcionalidade em um ambiente de desenvolvimento antes de ativar em produção.

## Contribuição

Sinta-se à vontade para enviar pull requests com melhorias ou correções. Para grandes mudanças, por favor, abra uma issue primeiro para discutir o que você gostaria de mudar.

## Suporte

Para suporte, entre em contato com [henrique.masters@gmail.com](mailto:henrique.masters@gmail.com).
