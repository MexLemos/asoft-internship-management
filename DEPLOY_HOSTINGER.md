# 🚀 Guia de Hospedagem na Hostinger & Deploy Automático via GitHub Actions
**Projeto:** Asoftmedia Internship Management System (AIMS)  
**Repositório GitHub:** `https://github.com/MexLemos/asoft-internship-management`

Este documento contém o passo a passo completo para hospedar a aplicação na **Hostinger** e configurar a integração contínua (CI/CD) para que qualquer alteração enviada com `git push origin main` seja publicada automaticamente no servidor, exatamente como no projeto **NewAsoftApp**.

---

## 📑 Índice
1. [Passo 1: Criar Subdomínio ou Domínio no hPanel](#passo-1-criar-subdomínio-ou-domínio-no-hpanel)
2. [Passo 2: Criar a Base de Dados MySQL](#passo-2-criar-a-base-de-dados-mysql)
3. [Passo 3: Importar a Estrutura da Base de Dados](#passo-3-importar-a-estrutura-da-base-de-dados)
4. [Passo 4: Envio Inicial dos Arquivos (Upload do ZIP)](#passo-4-envio-inicial-dos-arquivos-upload-do-zip)
5. [Passo 5: Configurar o Ficheiro `.env` em Produção](#passo-5-configurar-o-ficheiro-env-em-produção)
6. [Passo 6: Criar a Conta de FTP na Hostinger](#passo-6-criar-a-conta-de-ftp-na-hostinger)
7. [Passo 7: Configurar os Secrets no Repositório do GitHub](#passo-7-configurar-os-secrets-no-repositório-do-github)
8. [Passo 8: Testar o Deploy Automático](#passo-8-testar-o-deploy-automático)
9. [Credenciais Padrão de Primeiro Acesso](#credenciais-padrão-de-primeiro-acesso)

---

## Passo 1: Criar Subdomínio ou Domínio no hPanel

1. Aceda ao [hPanel da Hostinger](https://hpanel.hostinger.com/).
2. Vá em **Sites** > Selecione a sua hospedagem (ex: `softmedia-ao.com`).
3. Vá em **Domínios** > **Subdomínios**:
   - Crie o subdomínio desejado, por exemplo: `estagio` (ficando `estagio.softmedia-ao.com`).
   - A pasta raiz padrão do subdomínio será algo como:  
     `/domains/softmedia-ao.com/public_html/estagio` ou `/public_html/estagio`.
4. *(Opcional)* Se desejar que o subdomínio aponte diretamente para a pasta `public`:
   - No hPanel, pode definir a pasta de destino como `public_html/estagio/public`.
   - **Nota:** Graças ao ficheiro `.htaccess` na raiz do projeto, a aplicação funciona automaticamente mesmo se a pasta de destino for a raiz (`estagio`), pois o tráfego é redirecionado de forma segura para `public/`.

---

## Passo 2: Criar a Base de Dados MySQL

1. No menu do hPanel, clique em **Bases de Dados** > **Bases de Dados MySQL**.
2. No formulário **Criar Nova Base de Dados MySQL e Utilizador**:
   - **Nome da base de dados:** ex: `estagio` (a Hostinger colocará um prefixo, ex: `u629551196_estagio`).
   - **Nome de utilizador:** ex: `estagio_user` (ex: `u629551196_estagio_user`).
   - **Palavra-passe:** crie uma senha forte e anote-a.
3. Clique em **Criar**.

---

## Passo 3: Importar a Estrutura da Base de Dados

1. Ainda na tela de Bases de Dados, clique no botão **phpMyAdmin** ao lado da base de dados recém-criada.
2. Com o phpMyAdmin aberto, clique no separador **Importar** (no menu superior).
3. Clique em **Escolher ficheiro** e selecione o ficheiro consolidado gerado no projeto:
   - Caminho local: `database/database_production.sql`
4. Deixe o formato como **SQL** e clique em **Importar** (no fundo da página).
5. Todas as tabelas, permissões e contas administrativas padrão serão criadas instantaneamente.

---

## Passo 4: Envio Inicial dos Arquivos (Upload do ZIP)

Para garantir que todas as dependências (`vendor/`) subam com rapidez sem timeouts no FTP:
1. No hPanel da Hostinger, vá a **Ficheiros** > **Gestor de Ficheiros** (File Manager).
2. Navegue até a pasta do subdomínio (ex: `public_html/estagio`).
3. Clique no botão **Carregar / Upload** (canto superior direito) e selecione o arquivo:
   - `asoft_estagio_production.zip`
4. Após o upload, clique com o botão direito no ficheiro ZIP e selecione **Extrair** (Extract) para a própria pasta.
5. Elimine o arquivo `.zip` para poupar espaço em disco.

---

## Passo 5: Configurar o Ficheiro `.env` em Produção

1. No Gestor de Ficheiros, localize o ficheiro `.env.production`.
2. Renomeie-o para `.env` (ou crie um novo `.env`).
3. Edite o ficheiro e atualize os seguintes campos:
   ```dotenv
   APP_NAME="Asoftmedia Internship Management System"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://estagio.softmedia-ao.com
   APP_TIMEZONE=Africa/Luanda
   APP_SECRET=asoft_aims_prod_sec_994a37bd048e4cfbb29657c91206f

   # Dados reais da Hostinger
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=u629551196_estagio
   DB_USERNAME=u629551196_estagio_user
   DB_PASSWORD=Sua_Senha_Forte_Aqui
   ```
4. Guarde as alterações.

---

## Passo 6: Criar a Conta de FTP na Hostinger

1. No hPanel da Hostinger, vá a **Ficheiros** > **Contas FTP**.
2. Se já tiver uma conta FTP criada (a mesma usada no `NewAsoftApp`), pode reutilizar as mesmas credenciais!
3. Caso queira uma conta dedicada:
   - **Nome de utilizador:** ex: `estagio_deploy`
   - **Pasta:** indique a pasta de destino (ex: `/domains/softmedia-ao.com/public_html/estagio` ou `/public_html/estagio`)
   - **Palavra-passe:** defina uma senha.
4. Anote:
   - **Host FTP (Servidor):** ex: `ftp.softmedia-ao.com` ou o IP fornecido no hPanel.
   - **Porta:** `21`
   - **Utilizador FTP:** ex: `u629551196.estagio` ou o utilizador completo.

---

## Passo 7: Configurar os Secrets no Repositório do GitHub

1. Aceda ao repositório no GitHub:  
   👉 [https://github.com/MexLemos/asoft-internship-management](https://github.com/MexLemos/asoft-internship-management)
2. Clique em **Settings** (separador superior) > **Secrets and variables** > **Actions**.
3. Clique no botão verde **New repository secret** e adicione as seguintes variáveis:

| Nome do Secret | Valor / Descrição |
| :--- | :--- |
| `FTP_SERVER` | O host FTP da Hostinger (ex: `ftp.softmedia-ao.com` ou endereço IP) |
| `FTP_USERNAME` | O nome de utilizador do FTP da Hostinger |
| `FTP_PASSWORD` | A senha da conta de FTP |
| `FTP_SERVER_DIR` | O caminho da pasta na Hostinger (ex: `/domains/softmedia-ao.com/public_html/estagio/` ou `/public_html/estagio/`) |
| `FTP_PORT` *(Opcional)* | `21` (caso omitido, o workflow usa 21 por padrão) |

> ⚠️ **Atenção:** Certifique-se de que o valor de `FTP_SERVER_DIR` termina com uma barra `/`.

---

## Passo 8: Testar o Deploy Automático

1. No seu terminal local, faça um commit ou push:
   ```bash
   git add .
   git commit -m "feat: setup hostinger production deploy workflow"
   git push origin main
   ```
2. Abra o GitHub no seu navegador e aceda à aba **Actions**:
   - Verá o workflow **Deploy AIMS to Hostinger Production** em execução.
   - O GitHub sincronizará apenas os ficheiros modificados para a pasta da Hostinger em poucos segundos!
3. Acesse o seu subdomínio no navegador:
   - `https://estagio.softmedia-ao.com`

---

## Credenciais Padrão de Primeiro Acesso

Após importar o `database/database_production.sql`:

| Perfil | Nome de Utilizador | Palavra-passe Padrão | E-mail |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin` | `Password123!` | `superadmin@asoftmedia.ao` |
| **Administrador** | `admin` | `Password123!` | `admin@asoftmedia.ao` |
| **Supervisor Dev** | `carlos.silva` | `Password123!` | `carlos.silva@asoftmedia.ao` |
| **Supervisora Redes** | `ana.santos` | `Password123!` | `ana.santos@asoftmedia.ao` |

> 🔒 **Recomendação de Segurança:** Após o primeiro login no painel de administração, altere a palavra-passe do Super Admin e configure o e-mail oficial.
