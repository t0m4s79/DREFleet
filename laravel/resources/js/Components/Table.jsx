import React from 'react'

const Table = ({data, columns, editAction, dataId}) => {

    console.log('data', data)

    return (
        <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>
                        <table  className='items-center bg-transparent w-full border-collapse overflow-scroll'>
                            <thead className='border-b-2 overflow-scroll'>
                                <tr>
                                    {columns.map((col,index) => ( 
                                        <td key={index}>{col}</td>
                                    ))}
                                    <td>Ações</td>
                                </tr>
                            </thead>

                            <tbody>
                                {data.map((elem, i)=> (
                                    <tr key={elem[dataId]}>
                                        {(Object.values(elem)).map((value,index)=>(
                                            <td key={index}>{value}</td>
                                           
                                        ))}
                                        {console.log(elem.id)}
                                        <td className="px-6 py-4">
                                            <a href={route(editAction, elem.id)} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                            {/*<a href={route(deleteAction, elem.id)} className="font-medium text-red-600 dark:text-blue-500 hover:underline">Apagar</a>*/}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        
                    </div>

                </div>
            </div>
        </div>
    )
}

export default Table