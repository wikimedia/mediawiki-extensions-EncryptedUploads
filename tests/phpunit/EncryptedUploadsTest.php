<?php

namespace EncryptedUploads;

use MediaWikiIntegrationTestCase;

/**
 * Class EncryptedUploadsTest
 * @coversdefaultclass EncryptedUploads
 * @package EncryptedUploads
 * @group Database
 * @group FileRepo
 * @group FileBackend
 */
class EncryptedUploadsTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var EncryptedUploads
	 */
	private $encryptedUploads;

	/**
	 * @covers \EncryptedUploads\EncryptedUploads::generateRandomPassword
	 */
	public function testGenerateRandomPassword() {
		$password = $this->encryptedUploads->generateRandomPassword( 'test' );
		$validation = strlen( $password ) === 32 && ctype_xdigit( $password );
		$this->assertTrue( $validation );
	}

	/**
	 * @covers \EncryptedUploads\EncryptedUploads::encryptFile
	 */
	public function testEncryptFileIncorrect() {
		$result = $this->encryptedUploads->encryptFile( null, null, '123' );
		$this->assertFalse( $result );
	}

	/**
	 * @covers \EncryptedUploads\EncryptedUploads::encryptFile
	 */
	public function testEncryptFileCorrect() {
		$tmpFileSrc = $this->getNewTempFile();
		$tmpFileOut = $this->getNewTempFile();
		$result = $this->encryptedUploads->encryptFile( $tmpFileSrc, $tmpFileOut, '123' );
		$this->assertTrue( $result );
	}

	/**
	 * @covers \EncryptedUploads\EncryptedUploads::decryptFile
	 */
	public function testDecryptFileCorrect() {
		$tmpFileSrc = $this->getNewTempFile();
		$tmpFileOut = $this->getNewTempFile();
		$this->encryptedUploads->encryptFile( $tmpFileSrc, $tmpFileOut, '123' );
		$result = $this->encryptedUploads->decryptFile( $tmpFileOut, $tmpFileSrc, '123' );
		$this->assertTrue( $result );
	}

	/**
	 * @covers \EncryptedUploads\EncryptedUploads::decryptFile
	 */
	public function testDecryptingWithIncorrectPassword() {
		$tmpFileSrc = $this->getNewTempFile();
		$tmpFileOut = $this->getNewTempFile();
		$this->encryptedUploads->encryptFile( $tmpFileSrc, $tmpFileOut, '123' );
		$result = $this->encryptedUploads->decryptFile( $tmpFileOut, $tmpFileSrc, '1234' );
		$this->assertFalse( $result );
	}

	/**
	 * @covers \EncryptedUploads\EncryptedUploads::decryptFile
	 */
	public function testEncryptionDecryptionConsistent() {
		$tmpFileSrc = $this->getNewTempFile();
		$textOrig = 'test1234';
		file_put_contents( $tmpFileSrc, $textOrig );
		$tmpFileOut = $this->getNewTempFile();
		$this->encryptedUploads->encryptFile( $tmpFileSrc, $tmpFileOut, '123' );
		$this->encryptedUploads->decryptFile( $tmpFileOut, $tmpFileSrc, '123' );
		$text = file_get_contents( $tmpFileSrc );
		$this->assertEquals( $textOrig, $text );
	}

	protected function setUp(): void {
		parent::setUp();
		$this->encryptedUploads = EncryptedUploads::getInstance();
	}

	protected function tearDown(): void {
		parent::tearDown();
		EncryptedUploads::clear();
	}

}
