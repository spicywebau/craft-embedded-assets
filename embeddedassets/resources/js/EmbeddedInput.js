(function($)
{
	var EmbeddedInput = Garnish.Base.extend({

		assetInput: null,

		init: function(assetInput)
		{
			this.assetInput = assetInput;

			this.initThumbnails();
		},

		initThumbnails: function()
		{
			EmbeddedAssets.applyThumbnails(this.assetInput.$elements);

			var superResetElements = this.assetInput.resetElements;

			this.assetInput.resetElements = function()
			{
				superResetElements.apply(this, arguments);

				EmbeddedAssets.applyThumbnails(this.$elements);
			};
		}

	});

	EmbeddedAssets.patchClass(Craft.AssetSelectInput, EmbeddedInput);

	EmbeddedAssets.EmbeddedInput = EmbeddedInput;

})(jQuery);
