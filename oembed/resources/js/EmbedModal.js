(function($, Craft)
{
	var EmbedModal = Garnish.Modal.extend({

		init: function()
		{
			this.base();

			this.$form = $('<form class="modal fitted">').appendTo(Garnish.$bod);
			this.setContainer(this.$form);

			var body = $([
				'<div class="body">',
					'<div class="field">',
						'<div class="heading">',
							'<label for="oembed-url-field">', Craft.t('URL'), '</label>',
							'<div class="instructions"><p>', Craft.t('The link to the asset to embed.'), '</p></div>',
						'</div>',
						'<div class="input">',
							'<input id="oembed-url-field" type="text" class="text fullwidth">',
							'<ul id="oembed-url-errors" class="errors" style="display: none;"></ul>',
						'</div>',
					'</div>',
					'<div class="buttons right" style="margin-top: 0;">',
						'<div id="relabel-cancel-button" class="btn">', Craft.t('Cancel'), '</div>',
						'<input id="relabel-save-button" type="submit" class="btn submit" value="', Craft.t('Save'), '">',
					'</div>',
				'</div>'
			].join('')).appendTo(this.$form);

			this.$urlField = body.find('#oembed-url-field');
			this.$urlErrors = body.find('#oembed-url-errors');
			this.$cancelBtn = body.find('#relabel-cancel-button');
			this.$saveBtn = body.find('#relabel-save-button');

			this.$urlField.prop('placeholder', 'http://');

			this.addListener(this.$cancelBtn, 'click', 'hide');
			this.addListener(this.$form, 'submit', 'onFormSubmit');
		},

		onFormSubmit: function(e)
		{
			e.preventDefault();

			// Prevent multi form submits with the return key
			if(!this.visible)
			{
				return;
			}

			this.trigger('embed', {
				// TODO
			});

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
					$errorList = this.$urlField;

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

	window.OEmbed.EmbedModal = EmbedModal;

})(jQuery, Craft);
