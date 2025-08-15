{{$request = request()}}
Package: {{$request.package}}
{{if(!is.empty($request.module))}}
Module: {{$request.module|>string.uppercase.first}}

{{/if}}
{{if(!is.empty($request.submodule))}}
Submodule: {{$request.submodule|>string.uppercase.first}}

{{/if}}

{{binary()}} raxon_org/boot setup
