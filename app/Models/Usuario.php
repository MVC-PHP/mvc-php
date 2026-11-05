<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Usuario
{
	private PDO $conexao;

	public function __construct(?PDO $conexao = null){
		$this->conexao = $conexao ?? Database::getConnection();
	}

	public function buscarPorEmail(string $email): ?array{
		$consulta = $this->conexao->prepare(
		"SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = :email"
		);

		$consulta->execute(["email" => $email]);
		$usuario = $consulta->fetch();
		return $usuario === false ? null : $usuario;
	}
}

?>