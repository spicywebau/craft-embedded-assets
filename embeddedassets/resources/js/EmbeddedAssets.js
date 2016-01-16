(function($, Craft)
{
	/**
	 * EmbeddedAssets Class (Singleton)
	 */
	var EmbeddedAssets = new (Garnish.Base.extend({

		assetIndex: null,
		thumbnails: null, // Populated in EmbeddedAssetsPlugin.php

		init: function()
		{
			var that = this;
			var fn = Craft.AssetIndex.prototype;
			var superInit = fn.init;

			fn.init = function()
			{
				that.assetIndex = this;
				superInit.apply(this, arguments);

				that.setup();
			};
		},

		setup: function()
		{
			this.setupEmbedButton();
			this.setupThumbnails();
		},

		parseUrl: function(url)
		{
			var params = {
				url: url
			};

			Craft.postActionRequest('embeddedAssets/parseUrl', params, $.proxy(function(response, textStatus)
			{
				if(textStatus == 'success')
				{
					this.trigger('parseUrl', response);
				}
				else
				{
					Craft.cp.displayError(Craft.t('An unknown error occurred.'));
				}
			}, this));
		},

		saveAsset: function(params)
		{
			Craft.postActionRequest('embeddedAssets/saveEmbeddedAsset', params, $.proxy(function(response, textStatus)
			{
				if(textStatus == 'success')
				{
					this.trigger('saveAsset', response);

					console.log(response);
				}
				else
				{
					Craft.cp.displayError(Craft.t('An unknown error occurred.'));
				}
			}, this));
		},

		setupEmbedButton: function()
		{
			var assetIndex = this.assetIndex;
			var $buttonGroup = assetIndex.$uploadButton.parent()
				.removeClass('buttons')
				.addClass('btngroup');

			// Make sure the input is not the first (or last) element in the button group so border radii will be
			// applied correctly.
			assetIndex.$uploadInput.insertAfter(assetIndex.$uploadButton);

			var $embedButton = $('<div class="btn submit">')
				.text(Craft.t('Embed link'))
				.appendTo($buttonGroup);

			this.addListener($embedButton, 'click', 'openEmbedModal');
		},

		setupThumbnails: function()
		{
			var that = this;
			var assetIndex = this.assetIndex;

			assetIndex.on('updateElements', function(e)
			{
				var view = e.target.view;
				var elements = view.getAllElements();

				elements.each(function()
				{
					var $this = $(this);
					var id = $this.data('id') | 0;
					var thumbnail = that.getThumbnail(id);

					if(thumbnail)
					{
						var $img = $this.find('.elementthumb > img');
						$img.prop('srcset', thumbnail);
					}
				});
			});
		},

		openEmbedModal: function()
		{
			var modal = new EmbeddedAssets.EmbedModal();

			modal.on('saveAsset', $.proxy(this.onSaveAsset, this));
			modal.show();
		},

		getThumbnail: function(assetId)
		{
			return this.thumbnails && this.thumbnails[assetId] ? this.thumbnails[assetId] : null;
		},

		onSaveAsset: function(e)
		{
			this.saveAsset({
				folderId: this.assetIndex.getDefaultSourceKey().split(':')[1] | 0,
				media: e.media
			});
		}

	}))();

	window.EmbeddedAssets = EmbeddedAssets;

})(jQuery, Craft);
