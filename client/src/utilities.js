export function monkeypatch(Class, method, callback)
{
	const methodFn = Class.prototype[method]

	Class.prototype[method] = function()
	{
		methodFn.apply(this, arguments)
		callback.call(this)
	}
}

export function uniqueId(prefix = 'uid')
{
	return prefix + Math.random().toString(36).substr(2)
}

export function isUrl(url)
{
	return /^https?:\/\//.test(url)
}
