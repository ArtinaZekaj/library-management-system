# Library Management System

Ky projekt është zhvilluar për menaxhimin e një biblioteke. Sistemi ka tri role: Student, Operator dhe Super Admin, ku secili ka qasje në funksionalitete të ndryshme.

## Teknologjitë

- Backend: PHP, Laminas MVC, Doctrine ORM
- Frontend: React, Vite
- Database: PostgreSQL

## Instalimi

Për ekzekutimin e projektit nevojiten PHP, Composer, PostgreSQL, Node.js dhe npm.

### Backend

Nga folderi `backend`:

```bash
composer install
```

Krijoni databazën `library_management` në PostgreSQL.

Kopjoni `config/autoload/local.php.dist` në `config/autoload/local.php` dhe konfiguroni lidhjen me databazën.

Ekzekutoni Doctrine migrations për krijimin e tabelave.

Për të startuar backend-in:

```bash
composer serve
```

Backend: `http://localhost:8080`

### Frontend

Nga folderi `frontend`:

```bash
npm install
npm run dev
```

Frontend: `http://localhost:5173`
