-- Criação do banco e tabelas
CREATE DATABASE IF NOT EXISTS banco_curriculo;
USE banco_curriculo;

-- Tabela de alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de empresas
CREATE TABLE IF NOT EXISTS empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cnpj VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    status ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente'
);

-- Tabela de currículos dos alunos
CREATE TABLE IF NOT EXISTS curriculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    idade INT NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    curso VARCHAR(80) NOT NULL,
    periodo VARCHAR(50) NOT NULL,
    ano_ingresso INT,
    turno VARCHAR(30),
    objetivo TEXT,
    habilidades TEXT,
    experiencia TEXT,
    cursos TEXT,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de vagas
CREATE TABLE IF NOT EXISTS vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    requisitos TEXT,
    carga_horaria VARCHAR(50),
    salario VARCHAR(50),
    `local` VARCHAR(120),
    tipo VARCHAR(80),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATE,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de avisos
CREATE TABLE avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);