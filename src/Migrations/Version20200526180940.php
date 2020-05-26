<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200526180940 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cliente (id INT AUTO_INCREMENT NOT NULL, ci_ruc VARCHAR(15) NOT NULL, razon_social VARCHAR(255) NOT NULL, telefono VARCHAR(20) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalle_factura (id INT AUTO_INCREMENT NOT NULL, factura_id INT NOT NULL, producto_id INT NOT NULL, cantidad INT NOT NULL, INDEX IDX_B1354EA1F04F795F (factura_id), INDEX IDX_B1354EA17645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE empresa (id INT AUTO_INCREMENT NOT NULL, ruc VARCHAR(15) NOT NULL, razon_social VARCHAR(255) NOT NULL, direccion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE factura (id INT AUTO_INCREMENT NOT NULL, empresa_id INT NOT NULL, cliente_id INT NOT NULL, establecimiento VARCHAR(255) NOT NULL, punto_emision VARCHAR(255) NOT NULL, sec_factura VARCHAR(255) NOT NULL, fecha DATE NOT NULL, INDEX IDX_F9EBA009521E1991 (empresa_id), INDEX IDX_F9EBA009DE734E51 (cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, codigo VARCHAR(255) NOT NULL, detalle VARCHAR(255) NOT NULL, precio_unit DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE detalle_factura ADD CONSTRAINT FK_B1354EA1F04F795F FOREIGN KEY (factura_id) REFERENCES factura (id)');
        $this->addSql('ALTER TABLE detalle_factura ADD CONSTRAINT FK_B1354EA17645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE factura ADD CONSTRAINT FK_F9EBA009521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');
        $this->addSql('ALTER TABLE factura ADD CONSTRAINT FK_F9EBA009DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE factura DROP FOREIGN KEY FK_F9EBA009DE734E51');
        $this->addSql('ALTER TABLE factura DROP FOREIGN KEY FK_F9EBA009521E1991');
        $this->addSql('ALTER TABLE detalle_factura DROP FOREIGN KEY FK_B1354EA1F04F795F');
        $this->addSql('ALTER TABLE detalle_factura DROP FOREIGN KEY FK_B1354EA17645698E');
        $this->addSql('DROP TABLE cliente');
        $this->addSql('DROP TABLE detalle_factura');
        $this->addSql('DROP TABLE empresa');
        $this->addSql('DROP TABLE factura');
        $this->addSql('DROP TABLE producto');
    }
}
