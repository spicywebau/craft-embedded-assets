(function($, Craft)
{
	/**
	 * EmbeddedAssets Class (Singleton)
	 */
	var EmbeddedAssets = new (Garnish.Base.extend({

		assetIndex: null,
		thumbnails: {},

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

		getThumbnail: function(assetId, callback)
		{
			if(this.thumbnails.hasOwnProperty(assetId))
			{
				if(this.thumbnails[assetId])
				{
					callback(this.thumbnails[assetId]);
				}
			}
			else
			{
				var that = this;
				var url = Craft.getActionUrl('embeddedAssets/getThumbnail', { id: assetId });
				var image = new Image();

				image.onload = function()
				{
					that.thumbnails[assetId] = url;
					that.getThumbnail(assetId, callback)
				};

				image.onerror = function()
				{
					that.thumbnails[assetId] = false;
				};

				image.src = url;
			}
		},

		applyThumbnails: function($elements)
		{
			var that = this;

			$elements.each(function()
			{
				var $this = $(this);
				var id = $this.data('id') | 0;

				console.log(this)

				that.getThumbnail(id, function(url)
				{
					var $img = $this.find('.elementthumb > img');
					$img.prop('srcset', url);
				});
			});
		}

	}))();

	window.EmbeddedAssets = EmbeddedAssets;

})(jQuery, Craft);
