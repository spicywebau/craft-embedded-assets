(function($, Craft)
{
	/**
	 * EmbeddedAssets Class (Singleton)
	 */
	var EmbeddedAssets = new (Garnish.Base.extend({

		assetIndex: null,
		thumbnails: [], // Populated in EmbeddedAssetsPlugin.php

		patchClass: function(Patchee, Patcher)
		{
			var fn = Patchee.prototype;
			var init = fn.init;

			fn.init = function()
			{
				init.apply(this, arguments);
				new Patcher(this);
			};
		},

		parseUrl: function(url)
		{
			var params = {
				url: url
			};

			Craft.postActionRequest('embeddedAssets/parseUrl', params, $.proxy(function(response, textStatus)
			{
				if(textStatus == 'success' && response.success)
				{
					this.trigger('parseUrl', response);
				}
				else
				{
					var errors = response.errors && response.errors.length ?
						response.errors :
						[Craft.t('An unknown error occurred.')];

					for(var i = 0; i < errors.length; i++)
					{
						var error = errors[i];
						Craft.cp.displayError(error);
					}
				}
			}, this));
		},

		saveAsset: function(params)
		{
			Craft.postActionRequest('embeddedAssets/saveEmbeddedAsset', params, $.proxy(function(response, textStatus)
			{
				if(textStatus == 'success' && response.success)
				{
					var media = response.media;
					this.setThumbnail(media.id, media.thumbnailUrl);

					this.trigger('saveAsset', response);
				}
				else
				{
					var errors = response.errors && response.errors.length ?
						response.errors :
						[Craft.t('An unknown error occurred.')];

					for(var i = 0; i < errors.length; i++)
					{
						var error = errors[i];
						Craft.cp.displayError(error);
					}
				}
			}, this));
		},

		getThumbnail: function(assetId)
		{
			return this.thumbnails && this.thumbnails[assetId] ? this.thumbnails[assetId] : null;
		},

		setThumbnail: function(assetId, thumbnail)
		{
			if(!this.thumbnails)
			{
				this.thumbnails = {};
			}

			this.thumbnails[assetId | 0] = thumbnail;
		},

		applyThumbnails: function($elements)
		{
			var that = this;

			$elements.each(function()
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
		}

	}))();

	window.EmbeddedAssets = EmbeddedAssets;

})(jQuery, Craft);
