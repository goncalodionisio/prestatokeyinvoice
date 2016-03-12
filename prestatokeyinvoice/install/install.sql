
/*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Majoinfa - Sociedade Unipessoal Lda
 *  @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 *  @license   LICENSE.txt
 */
 
CREATE TABLE IF NOT EXISTS `PREFIX_prestatokeyinvoice_response` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `PREFIX_prestatokeyinvoice_response` (`code`,`message`)
VALUES
('1','Acção efectuada com sucesso.'),
('0','Código de resposta Não documentado.'),
('-1','Autenticação falhada. Verificar a Chave API_KEY'),
('-2','Faltam dados de configuração. Verifique a sua conta.'),
('-3','Não foi possível criar uma sessão (5 tentativas).'),
('-4','Sessão Expirada. Este código de sessão já Não é válido. (TTL 3600s )'),
('-5','Erro de parâmetros. Restrição de «Parâmetros Não vazios» Não respeitada'),
('-6','Funcionalidade indisponível para a sua licença.'),
('-12','Não foi possível gerar os dados do ficheiro para envio'),
('-101','Não foi possível gravar os dados do cliente!'),
('-102','Não foi possível encontrar o cliente pelo NIF indicado!'),
('-104','Não foi possível ler os dados do cliente!'),
('-105','Não foi possível eliminar o cliente!'),
('-111','Não foi possível criar novo registo de Morada Alternativa'),
('-112','Não foi possível alterar o registo de Morada Alternativa'),
('-113','Não foi possível carregar o registo de Morada Alternativa'),
('-114','Não foi possível apagar o registo de Morada Alternativa'),
('-121','Não foi possível carregar informação do cliente ao qual pretende associar a entidade'),
('-122','Não foi possível carregar informação da entidade indicada'),
('-201','Não foi possível gravar os dados do artigo!'),
('-202','Não foi possível ler os dados do artigo!'),
('-203','Não foi possível criar o artigo: esta referência já existe.'),
('-204','O artigo Não existe.'),
('-205','Não foi possível apagar o artigo.'),
('-206','Não foi possível gravar a taxa de IVA.'),
('-211','Não foi possível copiar a imagem a partir do URL indicado.'),
('-301','Não foi possível gravar o cabeçalho.'),
('-302','Não foi possível gravar a linha de documento.'),
('-303','Não foi possível ler os dados do cabeçalho para o código indicado.'),
('-304','Não foi possível gravar definitivamente o cabeçalho com o código indicado.'),
('-305','O documento com código indicado Não existe.'),
('-306','Não foi possível gravar a linha do documento.'),
('-307','Não foi possível guardar os detalhes do cabeçalho.'),
('-311','Não foi possível enviar o email. SMTP inactivo ou com configuração errada.'),
('-312','Não foi possível enviar o email. Erro na geração do ficheiro.'),
('-321','Ocorreu um erro na comunicação com a Autoriadade Tributária'),
('-322','Dados inválidos, ou funcionalidade inexistente no Sistema de Facturação.'),
('-331','Não foi possível adicionar a informação de Cor/Tamanho à linha do documento'),
('-401','Não foi possível gravar os dados do fornecedor!'),
('-402','Não foi possível encontrar o fornecedor pelo NIF indicado!'),
('-403','Não foi possível carregar os dados do fornecedor indicado'),
('-404','Não foi possível ler os dados do fornecedor!'),
('-405','Não foi possível eliminar o fornecedor!'),
('-501','Não foi possível criar o novo registo de cor/tamanho'),
('-511','O País indicado Não existe.'),
('-512','O País indicado já existe.'),
('-513','Não foi possível gravar o novo país.'),
('-521','Não foi possível gravar a nova moeda.'),
('-522','Não foi possível ler o registo de moeda indicado.'),
('-523','Não foi possível actualizar a moeda.'),
('-524','A moeda indicada Não existe.'),
('-525','Não foi possível apagar a moeda.'),
('-526','Não foi possível gravar um valor de conversão para esta moeda.'),
('-527','Não foi possível associar esta moeda como segunda moeda do documento.'),
('-528','Não foi possível gravar o documento com os dados de segunda moeda.'),
('-969','Existem avisos a verificar');
