import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({ auth, driver }) {

    console.log(driver)

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutor {driver.id}</h2>}
        >

            {/*<Head title={'Condutor'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        {driver.name}
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}