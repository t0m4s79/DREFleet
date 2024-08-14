import React from 'react'

const Table = ({data, columns}) => {

    

    return (
        <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>
                        <table  className='items-center bg-transparent w-full border-collapse'>
                            <thead>
                                <tr>
                                    {columns.map((col,index) => ( 
                                        <td key={index}>{col}</td>
                                    ))}
                                    <td>Ações</td>
                                </tr>
                            </thead>

                            <tbody>
                                {data.map((elem, i)=> (
                                    <tr key={i}>
                                        {(Object.values(elem)).map((value,index)=>(
                                            <td key={index}>{value}</td>
                                        ))}
                                        <td>Editar <br/> Apagar</td>
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