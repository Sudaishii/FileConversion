SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS benefeciaries;
DROP TABLE IF EXISTS certification;
DROP TABLE IF EXISTS overseas;
DROP TABLE IF EXISTS personal_data;

-- --------------------------------------------------------
-- MASTER TABLE (SOURCE OF account_number)
-- --------------------------------------------------------

CREATE TABLE personal_data (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  account_number BIGINT UNSIGNED NOT NULL,
  first_name VARCHAR(255) NOT NULL,
  middle_name VARCHAR(255) DEFAULT NULL,
  last_name VARCHAR(255) NOT NULL,
  dob DATE NOT NULL,
  sex VARCHAR(20) NOT NULL,
  civil_status VARCHAR(50) NOT NULL,
  nationality VARCHAR(100) NOT NULL,
  pob VARCHAR(255) NOT NULL,
  home_address VARCHAR(255) NOT NULL,
  mobile_number VARCHAR(15) NOT NULL,
  email_add VARCHAR(255) NOT NULL,
  suffix VARCHAR(50) DEFAULT NULL,
  father_fname VARCHAR(255) DEFAULT NULL,
  father_mname VARCHAR(255) DEFAULT NULL,
  father_lname VARCHAR(255) DEFAULT NULL,
  father_suffix VARCHAR(50) DEFAULT NULL,
  mother_fname VARCHAR(255) DEFAULT NULL,
  mother_mname VARCHAR(255) DEFAULT NULL,
  mother_lname VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_account_number (account_number)
) ENGINE=InnoDB;


-- --------------------------------------------------------
-- BENEFICIARIES
-- --------------------------------------------------------

CREATE TABLE benefeciaries (
  benef_id INT(11) NOT NULL AUTO_INCREMENT,
  account_number BIGINT UNSIGNED NOT NULL,
  spouse_fname VARCHAR(255) DEFAULT NULL,
  spouse_lname VARCHAR(255) DEFAULT NULL,
  child_fname VARCHAR(255) DEFAULT NULL,
  child_mname VARCHAR(255) DEFAULT NULL,
  child_lname VARCHAR(255) DEFAULT NULL,
  dob DATE DEFAULT NULL,
  other_fname VARCHAR(255) DEFAULT NULL,
  other_mname VARCHAR(255) DEFAULT NULL,
  other_lname VARCHAR(255) DEFAULT NULL,
  relation VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (benef_id),
  KEY idx_account_number (account_number),
  CONSTRAINT fk_benef_account
    FOREIGN KEY (account_number)
    REFERENCES personal_data (account_number)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- CERTIFICATION
-- --------------------------------------------------------

CREATE TABLE certification (
  account_number BIGINT UNSIGNED NOT NULL,
  printedName_path VARCHAR(255) DEFAULT NULL,
  signature_path VARCHAR(255) DEFAULT NULL,
  date_path VARCHAR(255) DEFAULT NULL,
  thumb_path VARCHAR(255) DEFAULT NULL,
  index_path VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (account_number),
  CONSTRAINT fk_cert_account
    FOREIGN KEY (account_number)
    REFERENCES personal_data (account_number)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- OVERSEAS
-- --------------------------------------------------------

CREATE TABLE overseas (
  account_number BIGINT UNSIGNED NOT NULL,
  profession_business VARCHAR(255) DEFAULT NULL,
  business_started VARCHAR(255) DEFAULT NULL,
  foreign_address VARCHAR(255) DEFAULT NULL,
  flexi_fund VARCHAR(255) DEFAULT NULL,
  monthly_earning INT(11) DEFAULT NULL,
  nws_ss_number BIGINT DEFAULT NULL,
  signature_path VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (account_number),
  CONSTRAINT fk_overseas_account
    FOREIGN KEY (account_number)
    REFERENCES personal_data (account_number)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
