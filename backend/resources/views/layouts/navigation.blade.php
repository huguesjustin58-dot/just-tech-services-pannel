<nav class="sticky top-0 z-40 border-b border-slate-800/80 bg-[#090D16]/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#00E5FF]/40 bg-[#00E5FF]/10 font-mono text-sm font-bold text-[#00E5FF]">
                    JTS
                </span>
                <span class="text-sm font-bold tracking-wider text-white sm:text-base">
                    JUST TECH SERVICES
                </span>
            </a>

            <div class="hidden items-center gap-4 text-sm font-medium md:flex">
                <a href="{{ route('home') }}" class="text-slate-300 transition hover:text-[#00E5FF]">
                    Forfaits &amp; Tarifs
                </a>

                @auth
                    @if(auth()->user()->isClient())
                        <a href="{{ route('client.dashboard') }}" class="text-slate-300 transition hover:text-[#00E5FF]">
                            Mon Espace Client
                        </a>
                    @endif

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-amber-300 transition hover:bg-amber-500/20">
                            Administration
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        <div class="flex items-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="rounded-lg border border-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-200 transition hover:border-[#00E5FF]/50 hover:text-white sm:text-sm">
                    Connexion
                </a>
                <a href="{{ route('register') }}" class="rounded-lg bg-[#00E5FF] px-3.5 py-2 text-xs font-bold text-[#090D16] transition hover:bg-cyan-300 sm:text-sm">
                    Créer un compte
                </a>
            @else
                <span class="hidden text-xs text-slate-400 sm:inline">
                    {{ auth()->user()->name }}
                </span>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ url('/admin') }}" class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-300 md:hidden">
                        Admin
                    </a>
                @elseif(auth()->user()->isClient())
                    <a href="{{ route('client.dashboard') }}" class="rounded-lg border border-cyan-500/30 bg-cyan-500/10 px-3 py-1.5 text-xs font-semibold text-[#00E5FF] md:hidden">
                        Espace Client
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-300 transition hover:border-rose-500/40 hover:text-rose-300">
                        Déconnexion
                    </button>
                </form>
            @endguest
        </div>
    </div>
</nav>
