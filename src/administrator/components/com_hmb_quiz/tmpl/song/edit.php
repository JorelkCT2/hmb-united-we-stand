<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Hmb\Component\Quiz\Administrator\View\Song\HtmlView $this */

HTMLHelper::_('behavior.formvalidator');

$hmbDoc = \Joomla\CMS\Factory::getApplication()->getDocument();
$hmbJs  = JPATH_ROOT . '/media/com_hmb_quiz/js/admin-video.js';
$hmbVer = is_file($hmbJs) ? filemtime($hmbJs) : '1';
$hmbDoc->addScript(\Joomla\CMS\Uri\Uri::root(true) . '/media/com_hmb_quiz/js/admin-video.js?v=' . $hmbVer, [], ['defer' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_hmb_quiz&view=song&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="song-form" class="form-validate">

	<div class="row">
		<div class="col-lg-8">
			<div class="card">
				<div class="card-body">
					<?php echo $this->form->renderField('content_type'); ?>
					<div id="hmbMovieFields">
						<?php echo $this->form->renderField('movie'); ?>
						<?php echo $this->form->renderField('original_title'); ?>
						<?php echo $this->form->renderField('movie_year'); ?>
						<?php echo $this->form->renderField('director'); ?>
					</div>
					<?php echo $this->form->renderField('title'); ?>
					<?php echo $this->form->renderField('band'); ?>
					<small id="hmbBandAuto" class="text-success d-block mb-2" hidden>✓ <?php echo Text::_('COM_HMB_QUIZ_BAND_AUTO'); ?></small>
					<?php echo $this->form->renderField('album'); ?>
					<?php echo $this->form->renderField('year'); ?>
					<?php echo $this->form->renderField('singer'); ?>
					<?php echo $this->form->renderField('genre'); ?>
					<?php echo $this->form->renderField('origin'); ?>
					<?php echo $this->form->renderField('difficulty'); ?>
					<?php echo $this->form->renderField('youtube_id'); ?>
					<small class="text-warning d-block mb-2">⚠ <?php echo Text::_('COM_HMB_QUIZ_AGE_HINT'); ?></small>

					<div class="control-group">
						<div class="control-label">
							<label><?php echo Text::_('COM_HMB_QUIZ_PREVIEW'); ?></label>
						</div>
						<div class="controls">
							<div id="hmbYtThumbWrap" class="mb-2"></div>
							<small class="text-muted"><?php echo Text::_('COM_HMB_QUIZ_PREVIEW_ID'); ?>: <code id="hmbYtId">&mdash;</code></small>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-4">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title"><?php echo Text::_('COM_HMB_QUIZ_TRAMO'); ?></h4>
					<?php echo $this->form->renderField('start_time'); ?>
					<?php echo $this->form->renderField('clip_length'); ?>
					<hr>
					<?php echo $this->form->renderField('cover_url'); ?>
					<small id="hmbCoverAuto" class="text-success d-block mb-1" hidden>✓ <?php echo Text::_('COM_HMB_QUIZ_COVER_AUTO'); ?></small>
					<div id="hmbCoverWrap" class="mb-2"></div>
					<div id="hmbPosterField">
						<hr>
						<?php echo $this->form->renderField('poster_url'); ?>
						<small id="hmbPosterAuto" class="text-success d-block mb-1" hidden>✓ <?php echo Text::_('COM_HMB_QUIZ_COVER_AUTO'); ?></small>
						<div id="hmbPosterWrap" class="mb-2"></div>
					</div>
					<hr>
					<?php echo $this->form->renderField('state'); ?>
					<?php echo $this->form->renderField('ordering'); ?>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
(function () {
	// --- YouTube preview ---
	var input = document.getElementById('jform_youtube_id');
	var idEl  = document.getElementById('hmbYtId');
	var wrap  = document.getElementById('hmbYtThumbWrap');

	function extract(v) {
		v = (v || '').trim();
		var m = v.match(/(?:youtu\.be\/|[?&]v=|\/embed\/|\/shorts\/|\/v\/)([A-Za-z0-9_-]{11})/);
		if (m) { return m[1]; }
		if (/^[A-Za-z0-9_-]{11}$/.test(v)) { return v; }
		return '';
	}

	function renderYt() {
		if (!input) { return; }
		var id = extract(input.value);
		if (idEl) { idEl.textContent = id || '\u2014'; }
		if (!wrap) { return; }
		if (id) {
			wrap.innerHTML =
				'<a href="https://www.youtube.com/watch?v=' + id + '" target="_blank" rel="noopener">' +
				'<img src="https://img.youtube.com/vi/' + id + '/hqdefault.jpg" alt="" ' +
				'style="max-width:340px;width:100%;border-radius:8px;border:1px solid #2a2a2a;display:block"></a>' +
				'<button type="button" class="btn btn-sm btn-secondary mt-2" id="hmbYtPlay">\u25B6 Previsualizar</button>';
			var play = document.getElementById('hmbYtPlay');
			if (play) {
				play.addEventListener('click', function () {
					wrap.innerHTML =
						'<div style="position:relative;max-width:480px;aspect-ratio:16/9">' +
						'<iframe src="https://www.youtube.com/embed/' + id + '" ' +
						'style="position:absolute;inset:0;width:100%;height:100%;border:0;border-radius:8px" ' +
						'allow="encrypted-media" allowfullscreen></iframe></div>';
				});
			}
		} else {
			wrap.innerHTML = '<span class="text-muted"><?php echo $this->escape(Text::_('COM_HMB_QUIZ_PREVIEW_HINT')); ?></span>';
		}
	}

	if (input) {
		var t;
		input.addEventListener('input', function () { clearTimeout(t); t = setTimeout(renderYt, 400); });
		renderYt();
	}

	// --- Cover preview ---
	var cInput = document.getElementById('jform_cover_url');
	var cWrap  = document.getElementById('hmbCoverWrap');

	function renderCover() {
		if (!cWrap) { return; }
		var u = (cInput.value || '').trim();
		if (u) {
			var img = document.createElement('img');
			img.src = u;
			img.alt = '';
			img.style.cssText = 'max-width:180px;width:100%;border-radius:8px;border:1px solid #2a2a2a;display:block';
			img.onerror = function () { cWrap.innerHTML = '<small class="text-danger"><?php echo $this->escape(Text::_('COM_HMB_QUIZ_COVER_BAD')); ?></small>'; };
			cWrap.innerHTML = '';
			cWrap.appendChild(img);
		} else {
			cWrap.innerHTML = '';
		}
	}

	if (cInput) {
		var ct;
		cInput.addEventListener('input', function () { clearTimeout(ct); ct = setTimeout(renderCover, 400); });
		renderCover();
	}

	// --- Movie poster preview ---
	var pInput = document.getElementById('jform_poster_url');
	var pWrap  = document.getElementById('hmbPosterWrap');
	var posterAuto = document.getElementById('hmbPosterAuto');

	function renderPoster() {
		if (!pWrap || !pInput) { return; }
		var u = (pInput.value || '').trim();
		if (u) {
			var img = document.createElement('img');
			img.src = u;
			img.alt = '';
			img.style.cssText = 'max-width:180px;width:100%;border-radius:8px;border:1px solid #2a2a2a;display:block';
			img.onerror = function () { pWrap.innerHTML = '<small class="text-danger"><?php echo $this->escape(Text::_('COM_HMB_QUIZ_COVER_BAD')); ?></small>'; };
			pWrap.innerHTML = '';
			pWrap.appendChild(img);
		} else {
			pWrap.innerHTML = '';
		}
	}

	if (pInput) {
		var pt;
		pInput.addEventListener('input', function () {
			clearTimeout(pt); pt = setTimeout(renderPoster, 400);
			if (posterAuto && pInput.value.trim() === '') { posterAuto.hidden = true; }
		});
		renderPoster();
	}

	// --- Auto-fill from songs already in the library ---
	var albumInput  = document.getElementById('jform_album');
	var bandInput   = document.getElementById('jform_band');
	var originInput = document.getElementById('jform_origin');
	var singerInput = document.getElementById('jform_singer');
	var genreInput  = document.getElementById('jform_genre');
	var yearInput   = document.getElementById('jform_year');
	var coverAuto   = document.getElementById('hmbCoverAuto');
	var bandAuto    = document.getElementById('hmbBandAuto');

	function setIfEmpty(input, val) {
		if (input && val && input.value.trim() === '') { input.value = val; return true; }
		return false;
	}

	function autoFill() {
		if (!albumInput && !bandInput) { return; }
		var album = albumInput ? albumInput.value.trim() : '';
		var band  = bandInput ? bandInput.value.trim() : '';
		if (album === '' && band === '') { return; }

		var url = 'index.php?option=com_hmb_quiz&task=song.findCover&format=json'
			+ '&album=' + encodeURIComponent(album) + '&band=' + encodeURIComponent(band);

		fetch(url, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				if (!d) { return; }
				if (d.cover && cInput && cInput.value.trim() === '') {
					cInput.value = d.cover;
					if (coverAuto) { coverAuto.hidden = false; }
					renderCover();
				}
				if (d.year && yearInput && yearInput.value.trim() === '') {
					yearInput.value = d.year;
					if (coverAuto) { coverAuto.hidden = false; }
				}
				var filled = false;
				if (setIfEmpty(originInput, d.origin)) { filled = true; }
				if (setIfEmpty(singerInput, d.singer)) { filled = true; }
				if (setIfEmpty(genreInput,  d.genre))  { filled = true; }
				if (filled && bandAuto) { bandAuto.hidden = false; }
			})
			.catch(function () {});
	}

	if (albumInput) { albumInput.addEventListener('change', autoFill); albumInput.addEventListener('blur', autoFill); }
	if (bandInput)  { bandInput.addEventListener('change', autoFill);  bandInput.addEventListener('blur', autoFill); }
	if (cInput) {
		cInput.addEventListener('input', function () { if (coverAuto && cInput.value.trim() === '') { coverAuto.hidden = true; } });
	}

	// --- Show movie-only fields when content type is "movie" ---
	var ctSel = document.getElementById('jform_content_type');
	var movieBox = document.getElementById('hmbMovieFields');
	var posterBox = document.getElementById('hmbPosterField');
	function toggleMovie() {
		var isMovie = (ctSel && ctSel.value === 'movie');
		if (movieBox) { movieBox.style.display = isMovie ? '' : 'none'; }
		if (posterBox) { posterBox.style.display = isMovie ? '' : 'none'; }
	}
	if (ctSel) { ctSel.addEventListener('change', toggleMovie); }
	toggleMovie();

	// --- Auto-fill movie data (original title, year, director, poster) from an existing entry of the same movie ---
	var movieInput = document.getElementById('jform_movie');
	var myInput    = document.getElementById('jform_movie_year');
	var dirInput   = document.getElementById('jform_director');
	var origTitle  = document.getElementById('jform_original_title');

	function autoFillMovie() {
		if (!movieInput) { return; }
		var mv = movieInput.value.trim();
		if (mv === '') { return; }

		var url = 'index.php?option=com_hmb_quiz&task=song.findMovie&format=json'
			+ '&movie=' + encodeURIComponent(mv);

		fetch(url, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				if (!d) { return; }
				setIfEmpty(myInput, d.movie_year);
				setIfEmpty(dirInput, d.director);
				setIfEmpty(origTitle, d.original_title);
				if (d.poster && pInput && pInput.value.trim() === '') {
					pInput.value = d.poster;
					if (posterAuto) { posterAuto.hidden = false; }
					renderPoster();
				}
			})
			.catch(function () {});
	}

	if (movieInput) {
		movieInput.addEventListener('change', autoFillMovie);
		movieInput.addEventListener('blur', autoFillMovie);
	}
})();
</script>
