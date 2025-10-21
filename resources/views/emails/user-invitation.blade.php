<x-mail::message>
    # Invitation à rejoindre {{ config('app.name') }}

    Vous avez été invité à créer un compte.

    <x-mail::button :url="$url">
        Créer mon compte
    </x-mail::button>

    Ce lien d'invitation expirera dans 7 jours.

    Si vous n'attendiez pas cette invitation, vous pouvez ignorer cet email en toute sécurité.

    Merci,<br>
    L'équipe {{ config('app.name') }}
</x-mail::message>