<div align="center">

# Treinamento Chat

**Sistema web de salas de treinamento com chat em tempo quase real, mensagens privadas, comunicados gerais, presença online e envio de áudio entre professores e alunos.**

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%2FMariaDB-Relational%20Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=111)
![HTML5](https://img.shields.io/badge/HTML5-Interface-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Responsive%20UI-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![License](https://img.shields.io/badge/License-Not%20defined-111827?style=for-the-badge)

</div>

---

## Visão Geral

O **Treinamento Chat** é uma aplicação PHP para comunicação operacional em ambientes de treinamento. O sistema organiza usuários em salas, separa perfis de **professor** e **aluno**, permite conversas privadas por sala, envio de mensagens gerais para todos os participantes e troca de mensagens de áudio gravadas diretamente pelo navegador.

A aplicação foi construída em PHP procedural, com páginas server-side renderizadas, endpoints PHP consumidos por `fetch()` no frontend e persistência relacional em MySQL/MariaDB. O comportamento de atualização de mensagens, presença online e contadores de não lidas é feito por polling JavaScript.

---

## Funcionalidades

| Área | Recursos disponíveis |
| --- | --- |
| Autenticação | Login por e-mail e senha, sessão PHP, separação de acesso por perfil `prof` e `user` |
| Salas | Criação de salas por professores, vínculo de alunos e professores, ativação/desativação de salas |
| Chat do professor | Lista de alunos da sala, mensagens privadas, comunicados gerais, áudio, contador de mensagens não lidas |
| Chat do aluno | Lista de professores da sala, mensagens privadas, recebimento de avisos gerais, áudio, contador de mensagens não lidas |
| Administração | Cadastro, edição e exclusão de usuários por professores com acesso administrativo |
| Presença online | Atualização periódica de atividade via endpoint `ping_online.php` |
| Upload de áudio | Gravação via `MediaRecorder`, envio em WebM, validação de tamanho e MIME no backend |
| Banco de dados | Schema relacional com usuários, professores, salas, participantes e mensagens |
| Segurança aplicada | Sessões, prepared statements em operações críticas, senhas novas com `password_hash`, sanitização do dump SQL |

---

## Arquitetura

A aplicação segue uma arquitetura simples e direta, orientada a páginas PHP e endpoints AJAX:

```text
Navegador
├── HTML/CSS renderizado por PHP
├── JavaScript vanilla
├── Fetch API para mensagens, listas, leitura e presença
└── MediaRecorder API para captura de áudio

Servidor PHP
├── Páginas autenticadas
├── Endpoints JSON/AJAX
├── Upload de áudio
├── Controle de sessão
└── Conexão MySQLi

MySQL/MariaDB
├── Login e perfis
├── Salas e participantes
├── Mensagens privadas/gerais/áudio
└── Estado online e leitura
```

---

## Estrutura do Projeto

```text
.
├── admin_usuarios.php
├── audios/
│   ├── .gitkeep
│   ├── enviar_audio_prof.php
│   └── enviar_audio_user.php
├── buscar_mensagem_user.php
├── buscar_privada_prof.php
├── chat_user.php
├── cone.php
├── database/
│   └── schema.sql
├── enviar_geral.php
├── enviar_mensagem_user.php
├── enviar_privada_prof.php
├── helpers.php
├── listar_alunos.php
├── listar_professores.php
├── login.php
├── logout.php
├── marcar_lidas_prof.php
├── marcar_lidas_user.php
├── ping_online.php
├── salas.php
├── tela_prof.php
├── toggle_sala.php
├── usuario_add.php
├── usuario_delete.php
├── usuario_edit.php
├── .env.example
├── .gitignore
└── README.md
```

| Caminho | Responsabilidade |
| --- | --- |
| `login.php` / `logout.php` | Entrada e saída de sessão |
| `helpers.php` | Inicialização de sessão e validação de usuário autenticado |
| `cone.php` | Configuração central da conexão MySQLi por variáveis de ambiente |
| `salas.php` | Dashboard de salas, criação de ambientes e seleção por perfil |
| `tela_prof.php` | Interface de chat do professor |
| `chat_user.php` | Interface de chat do aluno |
| `admin_usuarios.php` | Administração de usuários e perfis |
| `buscar_*.php` | Consulta incremental de mensagens |
| `enviar_*.php` | Envio de mensagens gerais, privadas e áudio |
| `listar_*.php` | Listagem de participantes e contadores de não lidas |
| `marcar_lidas_*.php` | Atualização de status de leitura |
| `ping_online.php` | Atualização periódica de presença online |
| `audios/` | Endpoints de upload e diretório runtime para arquivos WebM |
| `database/schema.sql` | Schema sanitizado do banco, sem dados pessoais ou senhas |

---

## Tecnologias

| Tecnologia | Uso no projeto | Referência |
| --- | --- | --- |
| PHP | Backend, views server-side, sessões e endpoints | https://www.php.net/ |
| MySQLi | Driver de acesso ao banco relacional | https://www.php.net/manual/en/book.mysqli.php |
| MySQL/MariaDB | Persistência das tabelas de usuários, salas e mensagens | https://www.mysql.com/ / https://mariadb.org/ |
| JavaScript Vanilla | Polling, navegação do chat, envio via Fetch API e gravação de áudio | https://developer.mozilla.org/docs/Web/JavaScript |
| Fetch API | Comunicação assíncrona com endpoints PHP | https://developer.mozilla.org/docs/Web/API/Fetch_API |
| MediaRecorder API | Gravação de áudio no navegador | https://developer.mozilla.org/docs/Web/API/MediaRecorder |
| HTML5/CSS3 | Interfaces de login, salas, chat e administração | https://developer.mozilla.org/docs/Web/HTML |
| Google Fonts | Tipografia Inter/Poppins nas telas | https://fonts.google.com/ |

Não foram detectados Composer, npm, Docker, filas, cache externo, WebSocket, workers ou serviços de cloud configurados neste repositório.

---

## Como Executar

### Pré-requisitos

- PHP 8.x com extensão `mysqli`
- MySQL ou MariaDB
- Navegador moderno com suporte a `MediaRecorder` para gravação de áudio

### Configuração

```bash
cp .env.example .env
```

Configure as variáveis do banco no ambiente do servidor ou no painel da hospedagem. Em ambiente local simples, o PHP também usa os valores padrão compatíveis com o arquivo `.env.example`.

### Banco de Dados

Crie o banco e importe o schema:

```sql
CREATE DATABASE treinamento CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE treinamento;
SOURCE database/schema.sql;
```

O schema atual não inclui usuários de exemplo porque o dump original continha dados pessoais e senhas em texto claro.

### Execução Local

Com PHP embutido:

```bash
php -S 127.0.0.1:8000
```

Acesse:

```text
http://127.0.0.1:8000/login.php
```

### Docker

Não há `Dockerfile` ou `docker-compose.yml` neste repositório.

### Build

Não há etapa de build detectada. O projeto é executado diretamente pelo servidor PHP.

### Testes

Não há suíte automatizada versionada. A validação disponível atualmente é sintática:

```bash
php -l arquivo.php
```

Para validar todos os arquivos PHP no Windows/PowerShell:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

---

## Variáveis de Ambiente

| Variável | Descrição |
| --- | --- |
| `APP_TIMEZONE` | Timezone usado pela aplicação PHP. Valor atual sugerido: `America/Sao_Paulo` |
| `DB_HOST` | Host do banco MySQL/MariaDB |
| `DB_PORT` | Porta do banco. Padrão: `3306` |
| `DB_NAME` | Nome do banco usado pela aplicação |
| `DB_USER` | Usuário de conexão com o banco |
| `DB_PASSWORD` | Senha do usuário de banco |

---

## Endpoints e Rotas

| Método | Endpoint | Perfil | Descrição |
| --- | --- | --- | --- |
| `GET/POST` | `/login.php` | Público | Renderiza login e autentica usuário |
| `GET` | `/logout.php` | Autenticado | Encerra sessão e marca usuário offline |
| `GET/POST` | `/salas.php` | Autenticado | Lista salas e cria novas salas para professores |
| `GET` | `/tela_prof.php?sala={id}` | Professor | Interface de chat do professor |
| `GET` | `/chat_user.php?sala={id}` | Aluno | Interface de chat do aluno |
| `GET` | `/admin_usuarios.php` | Professor | Gestão de usuários |
| `POST` | `/usuario_add.php` | Professor | Cria login e perfil de aluno/professor |
| `POST` | `/usuario_edit.php` | Professor | Atualiza nome, e-mail e tipo do usuário |
| `POST` | `/usuario_delete.php` | Professor | Remove usuário e perfil associado |
| `POST` | `/toggle_sala.php` | Professor | Ativa ou desativa uma sala |
| `GET` | `/listar_alunos.php?sala={id}` | Professor | Lista alunos da sala com presença e não lidas |
| `GET` | `/listar_professores.php?sala={id}` | Aluno | Lista professores da sala com presença e não lidas |
| `GET` | `/buscar_privada_prof.php` | Professor | Busca mensagens incrementais de um aluno |
| `GET` | `/buscar_mensagem_user.php` | Aluno | Busca mensagens incrementais de um professor |
| `POST` | `/enviar_geral.php` | Professor | Envia comunicado geral para a sala |
| `POST` | `/enviar_privada_prof.php` | Professor | Envia mensagem privada para aluno |
| `POST` | `/enviar_mensagem_user.php` | Aluno | Envia mensagem privada para professor |
| `POST` | `/audios/enviar_audio_prof.php` | Professor | Envia áudio privado para aluno |
| `POST` | `/audios/enviar_audio_user.php` | Aluno | Envia áudio privado para professor |
| `POST` | `/marcar_lidas_prof.php` | Professor | Marca mensagens de aluno como lidas |
| `POST` | `/marcar_lidas_user.php` | Aluno | Marca mensagens de professor como lidas |
| `POST` | `/ping_online.php` | Autenticado | Atualiza `last_ping` para presença online |

---

## Banco de Dados

O banco é relacional e centraliza autenticação, perfis, salas, participantes e mensagens.

| Tabela | Finalidade |
| --- | --- |
| `tb_login` | Credenciais, tipo de usuário, status ativo e metadados de conta |
| `tb_user` | Perfil de aluno vinculado a `tb_login` |
| `tb_professor` | Perfil de professor vinculado a `tb_login` |
| `salas` | Ambientes de treinamento criados por professores |
| `sala_users` | Associação entre salas e alunos |
| `sala_profs` | Associação entre salas e professores |
| `tb_mensagens` | Mensagens gerais, privadas e de áudio, com remetente, leitura e tempo de resposta |

Relacionamentos principais:

```text
tb_login 1:1 tb_user
tb_login 1:1 tb_professor
salas 1:N sala_users N:1 tb_user
salas 1:N sala_profs N:1 tb_professor
salas 1:N tb_mensagens
tb_professor 1:N tb_mensagens
tb_user 1:N tb_mensagens
```

---

## Segurança

Controles existentes no projeto:

- Sessões PHP com `ensureLoggedIn()` para bloquear páginas autenticadas.
- Separação de acesso por perfil (`prof` e `user`).
- Uso extensivo de prepared statements em consultas com entrada de usuário.
- Novas senhas cadastradas com `password_hash()`.
- Compatibilidade temporária no login para senhas antigas em texto claro, permitindo migração gradual.
- Sanitização de saída com `htmlspecialchars()` em telas principais.
- Validação de tamanho e MIME em uploads de áudio.
- `.gitignore` configurado para impedir versionamento de `.env`, caches e áudios gerados em runtime.
- Dump SQL sanitizado em `database/schema.sql`, sem registros reais, e-mails, mensagens ou senhas.

Pontos que ainda exigem evolução antes de produção:

- Remover compatibilidade com senha legada em texto claro após migração de usuários.
- Implementar proteção CSRF nos formulários e endpoints `POST`.
- Desativar `display_errors` em produção.
- Validar autorização de sala/participante em todos os endpoints AJAX.
- Criar política de retenção/limpeza para arquivos de áudio.

---

## Limpeza Aplicada no Repositório

| Item | Ação |
| --- | --- |
| Áudios versionados | Removidos de `audios/` por serem uploads gerados em runtime |
| Dump SQL com dados reais | Substituído por `database/schema.sql` contendo apenas estrutura |
| Arquivo SQL com nome confuso | Removido `treinamento (1).sql` |
| Credenciais/configuração | Conexão passou a ler variáveis de ambiente em `cone.php` |
| `.env` e arquivos sensíveis | Protegidos no `.gitignore` |
| Senhas novas | `usuario_add.php` passou a usar `password_hash()` |
| Queries interpoladas | Operações de exclusão/logout foram ajustadas para prepared statements |
| Upload de áudio | Inclusão de limite de 10 MB e validação de MIME |

---

## Qualidade e Testes

Foi realizada validação sintática com `php -l` em todos os arquivos PHP do projeto. Não há testes unitários, testes de integração, testes end-to-end ou ferramenta de cobertura configurados no repositório.

Recomendações de qualidade:

- Adicionar PHPUnit para regras de autenticação, permissões e persistência.
- Criar testes de integração para endpoints de chat e upload.
- Separar HTML/CSS/JS em camadas reutilizáveis.
- Padronizar encoding UTF-8 em todos os arquivos para corrigir textos acentuados corrompidos.
- Introduzir migrações versionadas para o schema do banco.

---

## Roadmap Técnico

- Criar camada de configuração central para ambiente local, homologação e produção.
- Migrar autenticação para senhas exclusivamente hasheadas.
- Adicionar CSRF tokens em formulários e chamadas sensíveis.
- Implementar WebSocket ou Server-Sent Events para reduzir polling.
- Criar `Dockerfile` e `docker-compose.yml` com PHP + MariaDB.
- Modularizar frontend em arquivos CSS/JS dedicados.
- Adicionar logs estruturados e tratamento padronizado de erros.
- Implementar controle granular de autorização por sala em todos os endpoints.

