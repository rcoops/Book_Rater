<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180324105249 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE books CHANGE isbn isbn VARCHAR(15) NOT NULL, CHANGE isbn_13 isbn_13 VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE authors ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, DROP firstName, DROP lastName');
        $this->addSql('ALTER TABLE users ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE messages CHANGE created_date created_date DATETIME NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0F57D20129');
        $this->addSql('DROP INDEX IDX_6970EB0F57D20129 ON reviews');
        $this->addSql('ALTER TABLE reviews ADD title VARCHAR(255) NOT NULL, ADD comments LONGTEXT NOT NULL, DROP review_title, DROP reviewComments, CHANGE book_reviewed_id book_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('CREATE INDEX IDX_6970EB0F16A2B381 ON reviews (book_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE authors ADD firstName VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD lastName VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP first_name, DROP last_name');
        $this->addSql('ALTER TABLE books CHANGE isbn isbn VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE isbn_13 isbn_13 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE messages CHANGE created_date created_date DATETIME DEFAULT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0F16A2B381');
        $this->addSql('DROP INDEX IDX_6970EB0F16A2B381 ON reviews');
        $this->addSql('ALTER TABLE reviews ADD reviewComments LONGTEXT NOT NULL COLLATE utf8_unicode_ci, DROP title, CHANGE book_id book_reviewed_id INT DEFAULT NULL, CHANGE comments review_title LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F57D20129 FOREIGN KEY (book_reviewed_id) REFERENCES books (id)');
        $this->addSql('CREATE INDEX IDX_6970EB0F57D20129 ON reviews (book_reviewed_id)');
        $this->addSql('ALTER TABLE users DROP name');
    }
}
