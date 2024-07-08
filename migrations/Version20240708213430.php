<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708213430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_loan (id INT AUTO_INCREMENT NOT NULL, book_id INT NOT NULL, loan_id INT NOT NULL, INDEX IDX_DC4E460B16A2B381 (book_id), INDEX IDX_DC4E460BCE73868F (loan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book_loan ADD CONSTRAINT FK_DC4E460B16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE book_loan ADD CONSTRAINT FK_DC4E460BCE73868F FOREIGN KEY (loan_id) REFERENCES loan (id)');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331CE73868F');
        $this->addSql('DROP INDEX IDX_CBE5A331CE73868F ON book');
        $this->addSql('ALTER TABLE book DROP loan_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_loan DROP FOREIGN KEY FK_DC4E460B16A2B381');
        $this->addSql('ALTER TABLE book_loan DROP FOREIGN KEY FK_DC4E460BCE73868F');
        $this->addSql('DROP TABLE book_loan');
        $this->addSql('ALTER TABLE book ADD loan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331CE73868F FOREIGN KEY (loan_id) REFERENCES loan (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_CBE5A331CE73868F ON book (loan_id)');
    }
}
