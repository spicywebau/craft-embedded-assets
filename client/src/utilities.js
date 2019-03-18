export function monkeypatch(Class, method, callback)
{
	const methodFn = Class.prototype[method]

	Class.prototype[method] = function()
	{
		methodFn.apply(this, arguments)
		callback.call(this)
	}
}

let uidCounter = 0
export function uniqueId(prefix = 'uid')
{
	return prefix + Math.random().toString(36).substr(2) + (uidCounter++)
}

export function isUrl(url)
{
	return /^https?:\/\//.test(url)
}

export function objectAssign(target)
{
	for (let i = 1; i < arguments.length; i++)
	{
		const source = arguments[i]

		for (let item in source) if (source.hasOwnProperty(item))
		{
			target[item] = source[item]
		}
	}

	return target
}
