import { Head } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import React from 'react'
import Table from '@/Components/Table';
import CustomDataGrid from '@/Components/CustomDataGrid';
import { parse } from 'date-fns';

export default function ShowApprovedOrders({auth, orders, userId}) {

	// Deconstruct order to just show relevant information
	const managerOrderInfo = orders.map((order)=> {
		return {
			id: order.id,
			begin_date: order.expected_begin_date,
			end_date: order.expected_end_date,
			approved_by: order.manager_id,
			approved_date: order.approved_date,
		}
	})

	const managerOrderColumns = {
		id: 'ID',
		begin_date: 'Data de início',
		end_date: 'Data de fim',
		approved_by: 'Aprovado por',
		approved_date: 'Data de Aprovação',
	}

	const ApprovedOrderColumns = [
		{
            field: 'id',
            headerName: 'ID do Pedido',
            flex: 1,
            //maxWidth: 60,
            hideable: false
        },
		{
			field: 'begin_date',
			headerName: 'Data de início',
            type: 'dateTime',
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'yyyy-MM-dd HH:mm:ss', new Date());
                return parsedDate
            },
		},
		{
			field: 'end_date',
			headerName: 'Data de fim',
            type: 'dateTime',
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'yyyy-MM-dd HH:mm:ss', new Date());
                return parsedDate
            },
		},
		{
            field: 'approved_by',
            headerName: 'Aprovado por',
            flex: 1,
        },
		{
            field: 'approved_date',
            headerName: 'Data de aprovação',
            type: 'dateTime',
            flex: 1,
            valueGetter: (params) => {
                if(params != '-'){
                    const parsedDate = parse(params, 'yyyy-MM-dd HH:mm:ss', new Date());
                    return parsedDate
                } else return null
            },
        },
	]

	return (
		<AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos aprovados pelo/a Gestor/a #{userId} </h2>}
        >

            <Head title="Pedidos Aprovados" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">


                    <Table data={managerOrderInfo} columnsLabel={managerOrderColumns} editAction={'orders.showEdit'} dataId="id"/>
                
					<CustomDataGrid 
						rows={managerOrderInfo}
						columns={ApprovedOrderColumns}
						editAction={'orders.showEdit'}
					/>
				</div>
            </div>
		</AuthenticatedLayout>
	)
}
