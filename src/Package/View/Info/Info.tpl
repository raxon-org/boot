{{translation.import()}}
{{$request = request()}}
Package: {{$request.package}}
{{if(!is.empty($request.module))}}Module: {{$request.module|>string.uppercase.first}}

{{/if}}
{{if(!is.empty($request.submodule))}}Submodule: {{$request.submodule|>string.uppercase.first}}

{{/if}}Commands:
[01] {{binary()}} {{$request.package}}

[02] {{binary()}} {{$request.package}} setup

Description:
[01] {{__('info')}}

[02] {{__('setup')}}

