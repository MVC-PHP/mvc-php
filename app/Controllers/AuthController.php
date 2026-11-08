<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Views\Render;

class AuthController
{
	private Usuario $model;
	
	public function __construct(){
		$this->model = new Usuario();
	}

	public function mostrarLogin(): string{
		return (new Render())->render("auth/login", ["titulo" => "Entrar"]);
	}

	public function entrar(): void{

		$email = trim($_POST["email"] ?? "");
		$senha = $_POST["senha"] ?? "";
		if ($email === "" || $senha === "") {
			$this->mensagem("danger", "Informe e-mail e senha.");
			$this->redirecionar("/login");
		}

		$usuario = $this->model->buscarPorEmail($email);
		if ($usuario === null || !password_verify($senha, $usuario["senha_hash"])) {
			$this->mensagem("danger", "E-mail ou senha inválidos.");
			$this->redirecionar("/login");
		}

		session_regenerate_id(true); 
		$_SESSION["usuario"] = [
			"id" => (int) $usuario["id"],
			"nome" => $usuario["nome"],
			"perfil" => $usuario["perfil"],
		];
		
		$this->mensagem("success", "Bem-vindo, " . $usuario["nome"] . "!");
		$this->redirecionar("/");
	}

	public function sair(): void{
		unset($_SESSION["usuario"]);
		session_regenerate_id(true);
		$this->mensagem("success", "Você saiu do sistema.");
		$this->redirecionar("/login");
	}

	private function mensagem(string $tipo, string $texto): void
	{
		$_SESSION["flash"] = [
			"tipo" => $tipo,
			"mensagem" => $texto,
		];
	}

	private function redirecionar(string $rota): void
	{
		header("Location: " . BASE_URL . $rota);
		exit;
	}
}
?>