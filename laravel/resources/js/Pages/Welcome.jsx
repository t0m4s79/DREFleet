import { Link, Head } from '@inertiajs/react';

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    const handleImageError = () => {
        document.getElementById('screenshot-container')?.classList.add('!hidden');
        document.getElementById('docs-card')?.classList.add('!row-span-1');
        document.getElementById('docs-card-content')?.classList.add('!flex-row');
        document.getElementById('background')?.classList.add('!hidden');
    };

    return (
        <>
            <Head title="Welcome" />
             {/* <div className="bg-white text-black/50 dark:bg-black dark:text-white/50"> */}
             <div className="bg-white text-black/50">
                <img
                    id="background"
                    className="absolute top-0 min-w-full h-32 max-h-32 md:h-[800px] md:max-h-[800px]"
                    src="/img/welcome_image.png"
                />
                <div className="relative min-h-screen flex flex-col items-center justify-center selection:bg-sky-500 selection:text-white">
                    <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                        <header className="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                            <div className="flex lg:justify-center lg:col-start-2">
                                <img 
                                    id='site logo'
                                    className="h-12 w-auto lg:h-16"
                                    src="/img/site_logo.svg"
                                />
                            </div>
                            <nav className="-mx-3 flex flex-1 justify-end">
                                {auth.user ? (
                                    <Link
                                        href={route('dashboard.index')}
                                        className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                    >
                                        Painel de Controlo
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={route('login')}
                                            className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                        >
                                            Log in
                                        </Link>
                                        <Link
                                            href={route('register')}
                                            className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                        >
                                            Register
                                        </Link>
                                    </>
                                )}
                            </nav>
                        </header>

                        <main className="mt-6">

                            <div className="text-center mt-24 mb-40">
                                        <h6 className="text-base font-light text-sky-500 text-center tracking-widest uppercase mb-2">
                                            Direção Regional de Educação
                                        </h6>
                                        <h2 className="text-8xl font-bold text-gray-800 text-center mb-8">
                                            Frotas DRE
                                        </h2>
                                        <p className="text-xl font-normal text-gray-800 text-center w-2/4 text-wrap m-auto">
                                            Aplicação digital para a gestão dos transportes realizados pela frota de veículos afetos à Direção Regional de Educação
                                        </p>
                                        <a
                                            href="/login"
                                            className="mt-10 bg-gradient-to-b from-sky-500 to-sky-800 hover:from-sky-400 hover:to-sky-700 focus:ring focus:ring-offset-1 focus:ring-sky-400 text-sm text-white shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] font-bold py-5 px-28 rounded-lg tracking-widest uppercase inline-block text-center"
                                            >
                                            Entrar
                                        </a>
                            </div>

                            <div className='flex flex-col lg:flex-row items-center mt-96 mb-56 gap-24'>
                                <div className='basis-5/12 text-center mx-auto'>
                                    <img className='object-fill'
                                    src="/img/about_image.svg"
                                    alt="img" 
                                    />
                                </div>
                                <div className='basis-7/12 pt-6'>
                                    <h2 className="text-4xl font-bold text-gray-800 mb-5">
                                        Sobre a frota
                                    </h2>
                                    <p className="text-lg font-normal text-gray-800 text-balance m-auto">
                                        A frota automóvel afeta à Direção Regional de Educação (DRE) é gerida pelo Núcleo de Equipamento e Conservação (NEC). O NEC é responsável, entre outras funções, por proceder à organização dos transportes e a escala dos assistentes operacionais afetos ao serviço de forma a assegurar, e com prioridade, o apoio ao transporte de crianças e jovens com necessidades educativas especiais. O NEC coordena também o transporte de trabalhadores e mercadorias. 
                                    </p>
                                </div>
                            </div>

                            <div className='flex flex-col lg:flex-row items-center mt-1 mb-56 gap-16'>
                                <div className='basis-1/3 text-center border border-slate-200 transition duration-300 hover:border-slate-300 rounded-lg py-16 px-5 shadow-sm'>
                                    <img className='mx-auto w-32 mb-10'
                                    src="/img/student_icon.svg"
                                    alt="img" 
                                    />
                                    <h2 className='text-2xl font-bold text-gray-800 mb-4'>
                                        Alunos
                                    </h2>
                                    <p className='text-base font-normal text-gray-400'>
                                        A aplicação simplifica a gestão logística e operacional do transporte de crianças e jovens de e para as escolas.
                                    </p>
                                </div>
                                <div className='basis-1/3 text-center border border-slate-200 transition duration-300 hover:border-slate-300 rounded-lg py-16 px-5 shadow-sm'>
                                    <img className='mx-auto w-32 mb-10'
                                    src="/img/driver_icon.svg"
                                    alt="img" 
                                    />
                                    <h2 className='text-2xl font-bold text-gray-800 mb-4'>
                                        Condutores
                                    </h2>
                                    <p className='text-base font-normal text-gray-400'>
                                        A aplicação permite a visualização rápida das rotas em tempo real para os condutores dos veículos em serviço.
                                    </p>
                                </div>
                                <div className='basis-1/3 text-center border border-slate-200 transition duration-300 hover:border-slate-300 rounded-lg py-16 px-5 shadow-sm'>
                                    <img className='mx-auto w-32 mb-10'
                                    src="/img/tech_icon.svg"
                                    alt="img" 
                                    />
                                    <h2 className='text-2xl font-bold text-gray-800 mb-4'>
                                        Técnicos
                                    </h2>
                                    <p className='text-base font-normal text-gray-400'>
                                        A aplicação facilita o preenchimento de relatórios e comunicação entre técnicos, condutores e administradores.
                                    </p>
                                </div>
                                
                            </div>

                            <div className="text-center mt-24 mb-40">
                                        <h2 className="text-4xl font-bold text-gray-800 text-center mb-7">
                                            A Direção Regional de Educação
                                        </h2>
                                        <p className="text-lg font-normal text-gray-800 text-center w-4/5 text-pretty m-auto">
                                            A Direção Regional de Educação (DRE) é o serviço da administração direta da Região Autónoma da Madeira integrado na Secretaria Regional de Educação, Ciência e Tecnologia (SRE), que promove, desenvolve, aplica e presta apoio às políticas educativas no âmbito pedagógico e didático da educação pré-escolar, dos ensinos básico e secundário, da educação extraescolar e da educação especial de toda a Região. A Direção de Serviços de Apoio à Gestão e Organização (DSAGO) é uma das unidades da DRE com funções de carácter predominantemente administrativo, na qual se integra o Núcleo de Equipamento e Conservação (NEC).
                                        </p>
                                        <a href='https://www.madeira.gov.pt/dre'> <button className="mt-10 bg-gradient-to-b from-sky-500 to-sky-800 hover:from-sky-400 hover:to-sky-700 focus:ring focus:ring-offset-1 focus:ring-sky-400 text-sm text-white shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] font-bold py-5 px-28 rounded-lg tracking-widest uppercase">
                                            Visitar site
                                        </button> </a>
                            </div>

                            <div className='bg-white shadow-[0px_14px_34px_0px_rgba(0,0,0,0.13)] rounded-lg mt-60 mb-60'>
                                <img className="py-10 px-16" src="/img/prr_logo.png" alt="PRR logos" />
                            </div>
                                
                        </main>

                        <footer className="py-16 text-center border-t border-gray-300 mt-10">
                            
                            <div className='text-base flex flex-col md:flex-row justify-center mb-10 gap-1 w-2/4 mx-auto text-gray-800'>
                                <a className='basis-1/3 grow-0 hover:underline decoration-gray-400 decoration-1 underline-offset-4' href='https://canaldenuncias.madeira.gov.pt/'>
                                    Canal de Denúncias
                                </a>
                                <a className='basis-1/3 grow-0 hover:underline decoration-gray-400 decoration-1 underline-offset-4' href='mailto:rgpd.dre@madeira.gov.pt'>
                                    Ponto de Contacto RGPD
                                </a>
                                <a className='basis-1/3 grow-0 hover:underline decoration-gray-400 decoration-1 underline-offset-4' href='https://privacidade.madeira.gov.pt/'>
                                    Política de Privacidade
                                </a>
                                
                            </div>
                            
                            <p className='text-gray-400 text-sm'>
                                © Frotas DRE
                            </p>
                            
                        </footer>
                    </div>
                </div>
            </div>
        </>
    );
}