<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200918073856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE charactercoding');
        $this->addSql('DROP INDEX ixwiagid ON familynamevariant');
        $this->addSql('DROP INDEX ixfamilyname ON familynamevariant');
        $this->addSql('ALTER TABLE familynamevariant CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE wiagid wiagid VARCHAR(31) NOT NULL');
        $this->addSql('DROP INDEX ixwiagid ON givennamevariant');
        $this->addSql('DROP INDEX ixgivenname ON givennamevariant');
        $this->addSql('ALTER TABLE givennamevariant CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE wiagid wiagid VARCHAR(31) NOT NULL');
        $this->addSql('DROP INDEX ixwiagid_person ON office');
        $this->addSql('DROP INDEX ixwiagid ON office');
        $this->addSql('ALTER TABLE office ADD id INT AUTO_INCREMENT NOT NULL, DROP sortkey, DROP titular_see, DROP profession, DROP id_monastery, CHANGE office_name office_name VARCHAR(63) DEFAULT NULL, CHANGE date_start date_start VARCHAR(31) NOT NULL, CHANGE wiagid wiagid VARCHAR(63) NOT NULL, CHANGE wiagid_person wiagid_person VARCHAR(63) NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE person CHANGE wiagid wiagid VARCHAR(63) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE charactercoding (id INT NOT NULL, name VARCHAR(63) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE familynamevariant CHANGE id id INT NOT NULL, CHANGE wiagid wiagid VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('CREATE INDEX ixwiagid ON familynamevariant (wiagid)');
        $this->addSql('CREATE INDEX ixfamilyname ON familynamevariant (familyname)');
        $this->addSql('ALTER TABLE givennamevariant CHANGE id id INT NOT NULL, CHANGE wiagid wiagid VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('CREATE INDEX ixwiagid ON givennamevariant (wiagid)');
        $this->addSql('CREATE INDEX ixgivenname ON givennamevariant (givenname)');
        $this->addSql('ALTER TABLE office MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE office DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE office ADD sortkey VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, ADD titular_see VARCHAR(63) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, ADD profession VARCHAR(63) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, ADD id_monastery VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, DROP id, CHANGE wiagid wiagid VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE wiagid_person wiagid_person VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE office_name office_name VARCHAR(127) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE date_start date_start VARCHAR(31) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('CREATE INDEX ixwiagid_person ON office (wiagid_person)');
        $this->addSql('CREATE INDEX ixwiagid ON office (wiagid)');
        $this->addSql('ALTER TABLE person CHANGE wiagid wiagid VARCHAR(31) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
    }
}
