<?php

namespace XFMG\Pub\View\Media;

use XF\Mvc\View;
use XF\Util\File;
use XFMG\Entity\MediaItem;

class Original extends View
{
	public function renderRaw()
	{
		/** @var MediaItem $mediaItem */
		$mediaItem = $this->params['mediaItem'];
		$attachment = $mediaItem->Attachment;

		if (!empty($this->params['return304']))
		{
			$this->response
				->httpCode(304)
				->removeHeader('last-modified');

			return '';
		}

		$this->response
			->setAttachmentFileParams($attachment->filename, $attachment->extension)
			->header('ETag', '"' . $attachment->attach_date . '"');

		// We don't store the file size of the original, so for display let's bring it in and serve it locally
		$tempFile = File::copyAbstractedPathToTempFile(
			$mediaItem->getOriginalAbstractedDataPath()
		);
		return $this->response->responseFile($tempFile);
	}
}
