# Asoftmedia Internship Management System (AIMS)

Sistema Web Integrado de **Gestão do Ciclo de Vida de Estagiários**, desenvolvido sob medida para a **Asoftmedia**.

---

## 🚀 Tecnologias Utilizadas

* **PHP 8.1+** (Strict Types, OOP, MVC, PSR-4, Prepared Statements)
* **MySQL 8.0+** (Base de dados relacional normalizada com InnoDB e transações ACID)
* **Bootstrap 5.3 & Bootstrap Icons** (Layout moderno, responsivo e Mobile-First)
* **Leaflet.js & OpenStreetMap** (Visualização e perímetros de geofence em mapas)
* **Chillerlan QR Code & Dompdf** (Geração de certificados digitais e validação criptográfica)
* **Composer** (Autoloading PSR-4 e gestão de dependências)

---

## 🏛 Arquitetura em Camadas

O sistema foi estruturado seguindo os princípios de Clean MVC e Separação de Responsabilidades:

$$\text{Apresentação (Views/Bootstrap)} \longrightarrow \text{Aplicação (Controllers/Middleware)} \longrightarrow \text{Domínio (Services/Engines)} \longrightarrow \text{Persistência (Models/PDO)}$$

Essa arquitetura garante que a aplicação não seja monolítica desorganizada e esteja **100% preparada para futura migração ou integração com Laravel, ASP.NET Core, React, Mobile Apps e Odoo**.

---

## 🔑 Contas de Demonstração (Seed Inicial)

| Perfil | Email / Nome de Utilizador | Palavra-passe | Finalidade |
| :--- | :--- | :--- | :--- |
| **Super Administrador** | `superadmin` ou `superadmin@asoftmedia.ao` | `Password123!` | Acesso total, configurações, geofence e auditoria |
| **Administrador** | `admin` ou `admin@asoftmedia.ao` | `Password123!` | Gestão de estagiários, instituições e certificados |
| **Supervisor (Dev)** | `carlos.silva` ou `carlos.silva@asoftmedia.ao` | `Password123!` | Avaliação de tarefas, GitHub PRs e competências |
| **Supervisor (Redes)** | `ana.santos` ou `ana.santos@asoftmedia.ao` | `Password123!` | Orientação técnica e acompanhamento |
| **Estagiário (Aluno)** | `joao.manuel` ou `joao.manuel@asoftmedia.ao` | `Password123!` | Marcação de ponto GPS, tarefas, Academia e portfólio |
| **Instituição (ISUTIC)** | `isutic_obs` ou `isutic@asoftmedia.ao` | `Password123!` | Observador académico dos seus alunos em estágio |
| **Instituição (ITEL)** | `itel_obs` ou `itel@asoftmedia.ao` | `Password123!` | Observador de frequência e notas |

---

## ⚙️ Instalação e Execução

### 1. Requisitos
* PHP 8.1 ou superior com extensões `pdo_mysql`, `mbstring`, `openssl`, `gd`.
* MySQL 8.0 ou superior (ex: Laragon / XAMPP / MariaDB).
* Composer 2.x.

### 2. Configurar o `.env`
Copie o ficheiro `.env.example` para `.env` e configure o banco de dados e as coordenadas da sede:
```env
DB_HOST=127.0.0.1
DB_DATABASE=asoft_estagio
DB_USERNAME=root
DB_PASSWORD=

COMPANY_LATITUDE=-8.83833
COMPANY_LONGITUDE=13.23444
COMPANY_RADIUS_METERS=100
```

### 3. Instalar Dependências e Executar Migrações
```bash
composer install
php database/DatabaseMigrator.php
php database/seeders/DatabaseSeeder.php
```

### 4. Iniciar o Servidor de Desenvolvimento
```bash
php -S localhost:8000 -t public
```
Aceda a `http://localhost:8000` no seu navegador.

---

## 🧪 Execução de Testes Automatizados
Para rodar a suíte de testes de integração (Banco, Auth, RBAC, Haversine GPS, Scoring e Certificados):
```bash
php tests/VerificationTest.php
```

---

## 🛡️ Funcionalidades Principais Implementadas

1. **Controlo de Presença por Geolocalização & Anti-Fraude**:
   - Captura de coordenadas GPS no dispositivo móvel.
   - Cálculo da distância no servidor via **Fórmula de Haversine**.
   - Bloqueio de tentativas fora do raio configurado (ex: 100m) e registo em tabela de tentativas suspeitas para auditoria.
   - Validação dos dias da semana permitidos para o estagiário.

2. **Sistema de Tarefas & Integração com GitHub**:
   - Atribuição com prioridades, prazos e categorias.
   - Submissão com URL de repositório GitHub, branch, commit e Pull Request.
   - Parecer técnico do supervisor com nota ponderada e thread de comentários.

3. **Academia Asoftmedia & Zona de Estudo**:
   - Trilha com Cursos, Módulos e Aulas.
   - Player com embeds legais do YouTube e leitor/download de manuais em PDF.
   - Testes online com banco de questões (múltipla escolha, V/F), correção automática e tentativas limitadas.

4. **Matriz de Competências & Motor de Desempenho**:
   - Avaliação de competências técnicas e comportamentais (Níveis 1 a 5).
   - Cálculo da nota ponderada configurável pelo painel administrativo.
   - Indicador dinâmico de risco (Normal 🟢, Atenção 🟡, Risco 🔴).

5. **Declaração Oficial & Validação por QR Code**:
   - Verificação de elegibilidade (Presença mínima de 80%, tarefas e testes).
   - Emissão de declaração de estágio com hash SHA-256 único.
   - QR Code apontando para página pública de validação (`/validar/{hash}`).

6. **Gamificação, Portfólio & LinkedIn**:
   - Conquistas e medalhas desbloqueáveis (Primeira Tarefa, Git Master, etc.).
   - Portfólio do aluno com gerador de publicação pré-formatada para o LinkedIn.

7. **Acesso para Instituições de Ensino**:
   - Portal em modo observador para acompanhamento do progresso, presença e notas dos seus alunos sem permissão de alteração de dados internos.

---

&copy; 2026 **Asoftmedia** - Todos os direitos reservados.
