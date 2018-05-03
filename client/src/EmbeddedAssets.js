import Modal from './Modal'

export default class EmbeddedAssets
{
	constructor()
	{
		this.modal = null
		this.buttons = []
	}

	create()
	{
		this.modal = new Modal({
			onHide: () => this.buttons.forEach(b => b.setActive(false)),
		})
	}

	destroy()
	{
		this.modal.destroy()
		this.modal = null
	}

	addButton(button, orientations = ['bottom', 'top', 'left', 'right'])
	{
		this.buttons.push(button)

		button.$element.on('click', (e) =>
		{
			if (this.modal)
			{
				this.buttons.forEach(b => b.setActive(b === button))
				this.modal.show(button.$element, { orientations })
			}
		})
	}

	removeButton(button)
	{
		this.buttons = this.buttons.filter(b => b !== button)
	}
}
