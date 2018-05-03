export function monkeypatch(Class, method, callback)
{
	const methodFn = Class.prototype[method]

	Class.prototype[method] = function()
	{
		methodFn.apply(this, arguments)
		callback(this)
	}
}

export function uniqueId()
{
	return 'uid' + Math.random().toString(36).substr(2)
}
