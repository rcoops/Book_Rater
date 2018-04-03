<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323223809 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE books (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, isbn VARCHAR(255) NOT NULL, isbn_13 VARCHAR(255) DEFAULT NULL, publisher VARCHAR(255) DEFAULT NULL, publish_date DATETIME DEFAULT NULL, edition INT DEFAULT NULL, average_rating INT DEFAULT NULL, UNIQUE INDEX UNIQ_4A1B2A92CC1CF4E6 (isbn), UNIQUE INDEX UNIQ_4A1B2A9228920213 (isbn_13), UNIQUE INDEX unique_book (title, edition), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE api_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(100) NOT NULL, notes LONGTEXT NOT NULL, INDEX IDX_2CAD560EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE authors (id INT AUTO_INCREMENT NOT NULL, firstName VARCHAR(255) NOT NULL, lastName VARCHAR(255) NOT NULL, initial VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE author_books (author_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_5C930A5FF675F31B (author_id), INDEX IDX_5C930A5F16A2B381 (book_id), PRIMARY KEY(author_id, book_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_1483A5E9C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, created_date DATETIME DEFAULT NULL, is_read TINYINT(1) DEFAULT \'0\', INDEX IDX_DB021E96A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reviews (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, book_reviewed_id INT DEFAULT NULL, review_title LONGTEXT NOT NULL, reviewComments LONGTEXT NOT NULL, rating INT NOT NULL, created_date DATETIME NOT NULL, edited_date DATETIME DEFAULT NULL, INDEX IDX_6970EB0FA76ED395 (user_id), INDEX IDX_6970EB0F57D20129 (book_reviewed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE api_tokens ADD CONSTRAINT FK_2CAD560EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE author_books ADD CONSTRAINT FK_5C930A5FF675F31B FOREIGN KEY (author_id) REFERENCES authors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE author_books ADD CONSTRAINT FK_5C930A5F16A2B381 FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F57D20129 FOREIGN KEY (book_reviewed_id) REFERENCES books (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE author_books DROP FOREIGN KEY FK_5C930A5F16A2B381');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0F57D20129');
        $this->addSql('ALTER TABLE author_books DROP FOREIGN KEY FK_5C930A5FF675F31B');
        $this->addSql('ALTER TABLE api_tokens DROP FOREIGN KEY FK_2CAD560EA76ED395');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96A76ED395');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FA76ED395');
        $this->addSql('DROP TABLE books');
        $this->addSql('DROP TABLE api_tokens');
        $this->addSql('DROP TABLE authors');
        $this->addSql('DROP TABLE author_books');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE reviews');
    }
}
