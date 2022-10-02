<?php

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}

require_once "$IP/maintenance/Maintenance.php";

use EncryptedUploads\EncryptedUploads;
use MediaWiki\MediaWikiServices;

// phpcs:disable MediaWiki.Files.ClassMatchesFilename.NotMatch
class EncryptedUploadsMassDecrypt extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Decrypts all the files to target directory' );
		$this->addOption( 'target', 'Target directory', true, true, 't' );
		$this->requireExtension( 'EncryptedUploads' );
	}

	/**
	 * @throws MWException
	 */
	public function execute() {
		$total = 0;

		$target = $this->getOption( 'target' );

		if ( !is_writable( $target ) ) {
			$this->fatalError( 'Target directory is not writable!' );
		}

		$files = $this->getDB( DB_REPLICA )->select(
			'encrypted_file',
			'*'
		);

		while ( $file = $files->fetchRow() ) {
			$repo = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo();
			$lf = LocalFile::newFromTitle( Title::newFromID( $file['page_id'] ), $repo );
			$this->output( "\nProcessing {$lf->getName()}" );

			$path = $lf->getRepo()->getBackend()->getLocalReference( [
				'src' => $lf->getPath()
			] );

			EncryptedUploads::getInstance()->decryptFile(
				$path->getPath(),
				$target . '/' . $lf->getName(),
				EncryptedUploads::getInstance()->getEncrypted( $file['page_id'] )->password
			);

			$total++;

		}

		$this->output( "\nTotal items processed: $total\n" . "Done!\n" );
	}
}

$maintClass = EncryptedUploadsMassDecrypt::class;
require_once RUN_MAINTENANCE_IF_MAIN;
