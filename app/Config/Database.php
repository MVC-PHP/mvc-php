<?php

namespace App\Config;

use PDO;

final class Database
{
    private static ?PDO $conexao = null;

    public static function getConnection(): PDO
    {
        if (self::$conexao instanceof PDO) {
            return self::$conexao;
        }

        $arquivo = dirname(__DIR__, 2) . "/config/database.php";
        $configuracaoLocal = is_file($arquivo) ? require $arquivo : [];

        if (!is_array($configuracaoLocal)) {
            throw new \RuntimeException("O arquivo config/database.php deve retornar um array.");
        }

        $host = self::configuracao("DB_HOST", $configuracaoLocal, "host", "127.0.0.1");
        $porta = self::configuracao("DB_PORT", $configuracaoLocal, "port", "3306");
        $banco = self::configuracao("DB_NAME", $configuracaoLocal, "database", "sistema_academico");
        $usuario = self::configuracao("DB_USER", $configuracaoLocal, "username", "root");
        $senha = self::configuracao("DB_PASSWORD", $configuracaoLocal, "password", "");
        $charset = self::configuracao("DB_CHARSET", $configuracaoLocal, "charset", "utf8mb4");

        $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset={$charset}";

        self::$conexao = new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$conexao;
    }

    /**
     * Alias temporario para codigo local que tenha usado a primeira versao da classe.
     */
    public static function conectar(): PDO
    {
        return self::getConnection();
    }

    private static function configuracao(
        string $variavel,
        array $configuracaoLocal,
        string $chave,
        string $padrao
    ): string
    {
        $valor = getenv($variavel);

        if ($valor !== false && $valor !== "") {
            return $valor;
        }

        return isset($configuracaoLocal[$chave])
            ? (string) $configuracaoLocal[$chave]
            : $padrao;
    }
}
