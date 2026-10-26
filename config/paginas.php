<?php

/*
|--------------------------------------------------------------------------
| Páginas estáticas (Sobre, Como funciona, Ajuda, Termos, Privacidade)
|--------------------------------------------------------------------------
| FONTE ÚNICA: o site renderiza estas páginas (rota /sobre, /termos...) e o app Flutter
| lê o mesmo conteúdo pela API (/api/v1/paginas). Edite aqui e os dois atualizam juntos.
|
| Cada seção pode ter: 'titulo', 'texto' (parágrafo) e/ou 'itens' (lista).
| Na página de ajuda, os itens são { p: pergunta, r: resposta }.
*/

return [

    'sobre' => [
        'titulo' => 'Sobre o SOS Trabalho',
        'icone' => 'info',
        'resumo' => 'Quem somos e por que o SOS Trabalho existe.',
        'secoes' => [
            [
                'titulo' => 'Nossa missão',
                'texto' => 'O SOS Trabalho conecta quem precisa de ajuda com quem quer trabalhar. De um bico de um dia a uma vaga fixa, o objetivo é aproximar pessoas e empresas da mesma região, de forma simples e direta.',
            ],
            [
                'titulo' => 'Para quem é',
                'itens' => [
                    'Pessoas que procuram trabalho, seja uma renda extra ou um novo emprego.',
                    'Pessoas que precisam de um serviço pontual (obra, mudança, limpeza, aulas...).',
                    'Empresas que querem divulgar vagas e encontrar candidatos perto de onde estão.',
                ],
            ],
            [
                'titulo' => 'O que você encontra aqui',
                'itens' => [
                    'Vagas organizadas por categoria e cidade.',
                    'Candidatura com mensagem para o anunciante.',
                    'Chat liberado entre quem anuncia e quem foi aprovado.',
                    'Avaliações para dar mais confiança nas contratações.',
                    'O mesmo cadastro no site e no aplicativo.',
                ],
            ],
        ],
    ],

    'como-funciona' => [
        'titulo' => 'Como funciona',
        'icone' => 'route',
        'resumo' => 'O passo a passo para quem procura e para quem anuncia.',
        'secoes' => [
            [
                'titulo' => 'Quero encontrar trabalho',
                'itens' => [
                    '1. Crie sua conta e complete seu perfil (profissão, cidade e uma breve apresentação).',
                    '2. Busque vagas por palavra-chave, cidade ou categoria e favorite as que interessarem.',
                    '3. Candidate-se e, se quiser, escreva uma mensagem para o anunciante.',
                    '4. Acompanhe o status: pendente, aprovada ou rejeitada. Você recebe um aviso a cada mudança.',
                    '5. Quando for aprovado, o chat com o anunciante é liberado para combinar os detalhes.',
                    '6. Depois do trabalho, avalie o anunciante.',
                ],
            ],
            [
                'titulo' => 'Quero anunciar uma vaga',
                'itens' => [
                    '1. Crie sua conta como pessoa ou como empresa (empresas informam o CNPJ).',
                    '2. Em "Anunciar vaga", preencha título, descrição, categoria, local e salário (opcional).',
                    '3. O anúncio fica ativo por 30 dias e pode ser renovado quando quiser.',
                    '4. Veja quem se candidatou, leia as mensagens e aprove ou rejeite.',
                    '5. Converse com os aprovados pelo chat e avalie quem trabalhou com você.',
                ],
            ],
            [
                'titulo' => 'Vagas vencidas',
                'texto' => 'Anúncios vencidos deixam de aparecer nas buscas e não recebem novas candidaturas. Quem anunciou pode renovar a vaga a qualquer momento.',
            ],
        ],
    ],

    'ajuda' => [
        'titulo' => 'Ajuda e perguntas frequentes',
        'icone' => 'help',
        'resumo' => 'Respostas rápidas para as dúvidas mais comuns.',
        'secoes' => [
            [
                'titulo' => 'Conta e perfil',
                'itens' => [
                    ['p' => 'Preciso de conta para me candidatar?', 'r' => 'Sim. Com a conta o anunciante sabe quem você é, e você acompanha suas candidaturas, favoritos e mensagens.'],
                    ['p' => 'Qual a diferença entre conta de pessoa e de empresa?', 'r' => 'A conta de empresa exige um CNPJ válido e aparece na lista de empresas parceiras. As duas podem anunciar vagas e se candidatar.'],
                    ['p' => 'Esqueci minha senha. E agora?', 'r' => 'A recuperação automática de senha ainda não está disponível. Entre em contato com a equipe do SOS Trabalho para ajuda.'],
                    ['p' => 'Minha conta do site funciona no aplicativo?', 'r' => 'Sim. O site e o aplicativo usam o mesmo cadastro, as mesmas vagas e as mesmas conversas.'],
                ],
            ],
            [
                'titulo' => 'Vagas e candidaturas',
                'itens' => [
                    ['p' => 'Por quanto tempo uma vaga fica no ar?', 'r' => 'Cada anúncio fica ativo por 30 dias. Depois disso ele expira, mas pode ser renovado por quem anunciou.'],
                    ['p' => 'Posso me candidatar à minha própria vaga?', 'r' => 'Não. Também não é possível se candidatar duas vezes à mesma vaga.'],
                    ['p' => 'Como cancelo uma candidatura?', 'r' => 'Em Perfil > Candidaturas, escolha a candidatura e toque em cancelar.'],
                ],
            ],
            [
                'titulo' => 'Chat e avaliações',
                'itens' => [
                    ['p' => 'Por que não consigo conversar com o anunciante?', 'r' => 'O chat é liberado somente depois que uma candidatura é aprovada. Isso protege os dois lados contra contatos indesejados.'],
                    ['p' => 'Quando posso avaliar alguém?', 'r' => 'Depois que uma candidatura entre vocês for aprovada. Você pode avaliar a pessoa uma vez por vaga.'],
                ],
            ],
        ],
    ],

    'termos' => [
        'titulo' => 'Termos de uso',
        'icone' => 'gavel',
        'resumo' => 'Regras para usar o SOS Trabalho.',
        'atualizado_em' => '21/10/2026',
        'secoes' => [
            [
                'titulo' => '1. Sobre o serviço',
                'texto' => 'O SOS Trabalho é uma plataforma que aproxima pessoas e empresas. Nós não somos parte da relação de trabalho: o combinado, o pagamento e as condições são de responsabilidade de quem anuncia e de quem aceita o trabalho.',
            ],
            [
                'titulo' => '2. Cadastro',
                'texto' => 'Você deve informar dados verdadeiros e manter sua senha em segurança. Empresas devem informar um CNPJ válido. Você é responsável pelas atividades feitas na sua conta.',
            ],
            [
                'titulo' => '3. Conteúdo e conduta',
                'itens' => [
                    'Anuncie apenas vagas e serviços reais e lícitos.',
                    'Não publique conteúdo ofensivo, discriminatório, enganoso ou que infrinja direitos de terceiros.',
                    'Não use a plataforma para golpes, cobrança antecipada indevida ou spam.',
                    'Avaliações devem refletir a experiência real.',
                ],
            ],
            [
                'titulo' => '4. Moderação',
                'texto' => 'Podemos remover anúncios, avaliações ou contas que violem estes termos, a lei ou a segurança de outros usuários.',
            ],
            [
                'titulo' => '5. Responsabilidade',
                'texto' => 'Recomendamos combinar valores, local e horário por escrito no chat e tomar cuidado ao se encontrar com pessoas desconhecidas. Não garantimos a contratação nem a qualidade dos serviços anunciados.',
            ],
            [
                'titulo' => '6. Alterações',
                'texto' => 'Estes termos podem ser atualizados. A data da última atualização aparece no topo desta página.',
            ],
        ],
    ],

    'privacidade' => [
        'titulo' => 'Política de privacidade',
        'icone' => 'lock',
        'resumo' => 'Quais dados usamos e para quê.',
        'atualizado_em' => '21/10/2026',
        'secoes' => [
            [
                'titulo' => 'Dados que coletamos',
                'itens' => [
                    'Cadastro: nome, e-mail, senha (guardada de forma criptografada) e, para empresas, o CNPJ.',
                    'Perfil: profissão, apresentação, cidade, estado e foto (opcionais).',
                    'Uso da plataforma: vagas anunciadas, candidaturas, favoritos, mensagens, avaliações e notificações.',
                ],
            ],
            [
                'titulo' => 'Para que usamos',
                'itens' => [
                    'Criar sua conta e manter você conectado.',
                    'Mostrar vagas, candidatos e perfis para quem usa a plataforma.',
                    'Enviar avisos sobre candidaturas, mensagens e avaliações.',
                ],
            ],
            [
                'titulo' => 'Localização das vagas',
                'texto' => 'Para mostrar a localização aproximada de uma vaga, a cidade e o bairro informados no anúncio são enviados ao serviço público OpenStreetMap (Nominatim). A lista de cidades vem do IBGE. O aplicativo não acompanha a sua posição em tempo real.',
            ],
            [
                'titulo' => 'O que fica visível para outros usuários',
                'texto' => 'Seu nome, profissão, apresentação, cidade, foto e avaliações recebidas aparecem no seu perfil público. Seu e-mail só é mostrado ao anunciante quando você se candidata a uma vaga dele. Mensagens do chat só são visíveis para os dois participantes.',
            ],
            [
                'titulo' => 'Seus direitos',
                'texto' => 'Você pode editar os dados do seu perfil a qualquer momento, cancelar candidaturas, excluir suas vagas e remover avaliações que fez. Para outros pedidos sobre seus dados, fale com a equipe do SOS Trabalho.',
            ],
        ],
    ],
];
