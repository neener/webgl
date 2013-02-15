precision highp float;

////////////////////////////////////////////////////////////////
attribute vec4	_vPosition;
varying vec2	_UV;

void	main()
{
	gl_Position = _vPosition;
	_UV = 0.5 * (1.0 + _vPosition.xy);
}
