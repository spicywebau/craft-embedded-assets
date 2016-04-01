(function($)
{
	var EmbedModal = Garnish.Modal.extend({

		media: null,
		currentUrl: null,

		init: function()
		{
			this.base();

			this.$form = $('<form class="modal fitted">').appendTo(Garnish.$bod);
			this.setContainer(this.$form);

			var body = $([
				'<div class="body">',
					'<div class="field">',
						'<div class="heading">',
							'<label for="embeddedassets-url-field">', Craft.t('URL'), '</label>',
							'<div class="instructions"><p>', Craft.t('The link to the asset to embed.'), '</p></div>',
						'</div>',
						'<div id="embeddedassets-url" class="input">',
							'<div id="embeddedassets-url-spinner" class="spinner" style="display: none;"></div>',
							'<input id="embeddedassets-url-field" type="text" class="text fullwidth" autocomplete="off">',
							'<ul id="embeddedassets-url-errors" class="errors" style="display: none;"></ul>',
						'</div>',
					'</div>',
					'<a id="embeddedassets-media" class="hidden disabled" target="_blank">',
						'<div id="embeddedassets-media-image"></div>',
						'<div id="embeddedassets-media-content">',
							'<p id="embeddedassets-media-title"></p>',
							'<p id="embeddedassets-media-description"></p>',
							'<mark id="embeddedassets-media-type"></mark>',
						'</div>',
					'</a>',
					'<div class="buttons right" style="margin-top: 0;">',
						'<div id="embeddedassets-cancel-button" class="btn">', Craft.t('Cancel'), '</div>',
						'<input id="embeddedassets-save-button" type="submit" class="btn submit disabled" disabled value="', Craft.t('Save'), '">',
					'</div>',
				'</div>'
			].join('')).appendTo(this.$form);

			this.$url = body.find('#embeddedassets-url');
			this.$urlSpinner = body.find('#embeddedassets-url-spinner');
			this.$urlField = body.find('#embeddedassets-url-field');
			this.$urlErrors = body.find('#embeddedassets-url-errors');
			this.$media = body.find('#embeddedassets-media');
			this.$mediaImage = body.find('#embeddedassets-media-image');
			this.$mediaTitle = body.find('#embeddedassets-media-title');
			this.$mediaDesc = body.find('#embeddedassets-media-description');
			this.$mediaType = body.find('#embeddedassets-media-type');
			this.$cancelBtn = body.find('#embeddedassets-cancel-button');
			this.$saveBtn = body.find('#embeddedassets-save-button');

			this.$urlField.prop('placeholder', 'http://');

			this.addListener(this.$urlField, 'change', 'onUrlChange');
			this.addListener(this.$urlField, 'blur', 'onUrlChange');
			this.addListener(this.$urlField, 'paste', 'onUrlPaste');
			this.addListener(this.$cancelBtn, 'click', 'hide');
			this.addListener(this.$form, 'submit', 'onFormSubmit');

			EmbeddedAssets.on('parseUrl', $.proxy(this.onParseUrl, this));
		},

		onUrlChange: function(e)
		{
			var url = this.$urlField.val();

			this.parseUrl(url);
		},

		onUrlPaste: function(e)
		{
			var clipboardData = e.clipboardData || e.originalEvent.clipboardData || window.clipboardData;
			var url = clipboardData.getData('text');

			this.parseUrl(url);
		},

		parseUrl: function(url)
		{
			if(this.currentUrl != url)
			{
				this.currentUrl = url;

				this.displayErrors('url', false);

				if(url)
				{
					this.$urlSpinner.show();
					this.$media.addClass('disabled');

					EmbeddedAssets.parseUrl(url);
				}
				else
				{
					this.$urlSpinner.hide();
					this.$media.addClass('hidden disabled');
					
					this.updateSizeAndPosition();
				}
			}
		},

		onParseUrl: function(e)
		{
			var media = e.media;

			this.url = e.url;
			this.media = media;

			if(e.success)
			{
				this.$media.prop('href', media.url);
				this.$mediaTitle.html(media.title || Craft.t("No title")).toggleClass('empty', !media.title);
				this.$mediaDesc.html(media.description || Craft.t("No description")).toggleClass('empty', !media.description);
				this.$mediaImage.css('backgroundImage', 'url(' + media.thumbnailUrl + ')');
				this.$mediaType.text(media.type);

				this.$media.removeClass('hidden disabled');
			}

			this.$urlSpinner.hide();
			this.$saveBtn.toggleClass('disabled', !e.success).prop('disabled', !e.success);
			this.$media.toggleClass('hidden', !e.success);
			this.displayErrors('url', e.errors);

			this.updateSizeAndPosition();
		},

		onFormSubmit: function(e)
		{
			e.preventDefault();

			// Prevent multi form submits with the return key
			if(!this.visible)
			{
				return;
			}

			if(this.media)
			{
				this.trigger('saveAsset', {
					url: this.url,
					media: this.media
				});
			}

			this.hide();
		},

		onFadeOut: function()
		{
			this.base();

			this.destroy();
		},

		destroy: function()
		{
			this.base();

			this.$container.remove();
			this.$shade.remove();
		},

		show: function()
		{
			if(!Garnish.isMobileBrowser())
			{
				setTimeout($.proxy(function()
				{
					this.$urlField.focus()
				}, this), 100);
			}

			this.base();
		},

		displayErrors: function(attr, errors)
		{
			var $input;
			var $errorList;

			switch(attr)
			{
				case 'url':
				{
					$input = this.$urlField;
					$errorList = this.$urlErrors;

					break;
				}
			}

			$errorList.children().remove();

			if(errors)
			{
				$input.addClass('error');
				$errorList.show();

				for(var i = 0; i < errors.length; i++)
				{
					$('<li>').text(errors[i]).appendTo($errorList);
				}
			}
			else
			{
				$input.removeClass('error');
				$errorList.hide();
			}
		}
	});

	EmbeddedAssets.EmbedModal = EmbedModal;

})(jQuery);
